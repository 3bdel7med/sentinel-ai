<?php

namespace Abdelhmed\SentinelAi\Http\Controllers;

use Abdelhmed\SentinelAi\Exceptions\SentinelException;
use Abdelhmed\SentinelAi\Models\SentinelLog;
use Abdelhmed\SentinelAi\Services\GeminiClient;
use Abdelhmed\SentinelAi\Services\Redactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Throwable;

class SentinelController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), ['open', 'resolved', 'all'], true)
            ? $request->query('status')
            : 'open';

        $search = $request->string('q')->toString();

        $query = SentinelLog::query()->search($search);

        if ($status === 'open') {
            $query->open();
        } elseif ($status === 'resolved') {
            $query->resolved();
        }

        $logs = $query->orderByDesc('last_seen_at')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        return view('sentinel::index', [
            'logs'       => $logs,
            'status'     => $status,
            'search'     => $search,
            'counts'     => [
                'open'     => SentinelLog::open()->count(),
                'resolved' => SentinelLog::resolved()->count(),
            ],
            'hasPending' => $logs->contains(fn ($log) => $log->ai_status === 'pending'),
            'hasKey'     => filled(config('sentinel.gemini_api_key')),
        ]);
    }

    public function resolve(SentinelLog $log): RedirectResponse
    {
        $log->update(['resolved_at' => $log->resolved_at ? null : now()]);

        return back();
    }

    /** Re-run the explanation (button on the dashboard, runs while the user waits). */
    public function analyze(SentinelLog $log, GeminiClient $client, Redactor $redactor): RedirectResponse
    {
        try {
            $log->update([
                'ai_analysis' => $client->analyze($log),
                'ai_status'   => 'done',
                'ai_error'    => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'ai_status' => 'failed',
                'ai_error'  => $this->safeMessage($e, $redactor),
            ]);
        }

        return back();
    }

    /** Test generation is on demand only, so tokens are not spent on every error. */
    public function test(SentinelLog $log, GeminiClient $client, Redactor $redactor): RedirectResponse
    {
        try {
            $log->update(['ai_test' => $client->generateTest($log)]);
        } catch (Throwable $e) {
            return back()->with('sentinel_error', $this->safeMessage($e, $redactor));
        }

        return back();
    }

    public function destroy(SentinelLog $log): RedirectResponse
    {
        $log->delete();

        return back();
    }

    /** DELETE /sentinel          -> everything
     *  DELETE /sentinel?scope=resolved -> only resolved errors */
    public function clear(Request $request): RedirectResponse
    {
        $query = SentinelLog::query();

        if ($request->query('scope') === 'resolved') {
            $query->resolved();
        }

        $query->delete();

        return redirect()->route('sentinel.index');
    }

    protected function safeMessage(Throwable $e, Redactor $redactor): string
    {
        $message = $e instanceof SentinelException
            ? $e->getMessage()
            : 'Unexpected error while contacting the AI service.';

        return Str::limit((string) $redactor->redact($message), 500);
    }
}
