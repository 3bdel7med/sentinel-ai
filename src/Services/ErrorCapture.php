<?php

namespace Abdelhmed\SentinelAi\Services;

use Abdelhmed\SentinelAi\Exceptions\SentinelException;
use Abdelhmed\SentinelAi\Jobs\AnalyzeErrorJob;
use Abdelhmed\SentinelAi\Models\SentinelLog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns an exception into ONE database row (deduplicated) and, for new
 * errors only, schedules the AI analysis.
 */
class ErrorCapture
{
    /** Guards against recursion if capturing itself fails. */
    protected static bool $capturing = false;

    public function __construct(protected Redactor $redactor)
    {
    }

    public function capture(Throwable $e): void
    {
        if (static::$capturing || ! $this->shouldCapture($e)) {
            return;
        }

        static::$capturing = true;

        try {
            $hash = sha1(get_class($e) . '|' . $e->getFile() . '|' . $e->getLine());

            if ($existing = SentinelLog::where('hash', $hash)->first()) {
                $this->bump($existing);
                return;
            }

            try {
                $log = SentinelLog::create($this->payload($e, $hash));
            } catch (QueryException) {
                // Another request inserted the same hash first (unique index).
                if ($existing = SentinelLog::where('hash', $hash)->first()) {
                    $this->bump($existing);
                }
                return;
            }

            $this->scheduleAnalysis($log);
        } finally {
            static::$capturing = false;
        }
    }

    protected function shouldCapture(Throwable $e): bool
    {
        if (! config('sentinel.enabled')) {
            return false;
        }

        if (! app()->environment((array) config('sentinel.environments', ['local']))) {
            return false;
        }

        // Never capture our own failures (would loop forever).
        if ($e instanceof SentinelException) {
            return false;
        }

        foreach ((array) config('sentinel.ignore', []) as $class) {
            if ($e instanceof $class) {
                return false;
            }
        }

        return true;
    }

    protected function bump(SentinelLog $log): void
    {
        $log->increment('occurrences');
        // A resolved error that happens again is re-opened.
        $log->update(['last_seen_at' => now(), 'resolved_at' => null]);
    }

    protected function payload(Throwable $e, string $hash): array
    {
        $request = app()->runningInConsole() ? null : request();

        return [
            'hash'            => $hash,
            'exception_class' => get_class($e),
            'message'         => $this->redactor->redact($e->getMessage()) ?: '(no message)',
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
            'trace'           => $this->redactor->redact(
                Str::limit($e->getTraceAsString(), (int) config('sentinel.trace_limit', 4000), '...')
            ),
            'code_snippet'    => $this->snippet($e->getFile(), $e->getLine()),
            'method'          => $request?->method(),
            // url() has no query string, so tokens in ?query=... are never stored.
            'url'             => $request ? Str::limit($request->url(), 2000, '') : null,
            'environment'     => app()->environment(),
            'occurrences'     => 1,
            'last_seen_at'    => now(),
            'ai_status'       => $this->canAnalyze() ? 'pending' : 'skipped',
        ];
    }

    /** @return array<int, string>|null  line number => code */
    protected function snippet(string $file, int $line): ?array
    {
        if (! is_file($file) || ! is_readable($file)) {
            return null;
        }

        $lines = @file($file, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return null;
        }

        $radius = (int) config('sentinel.snippet_radius', 10);
        $start  = max($line - $radius, 1);
        $end    = min($line + $radius, count($lines));

        $out = [];
        for ($i = $start; $i <= $end; $i++) {
            $out[$i] = $this->redactor->redact($lines[$i - 1]);
        }

        return $out;
    }

    protected function canAnalyze(): bool
    {
        return config('sentinel.auto_analyze') && filled(config('sentinel.gemini_api_key'));
    }

    protected function scheduleAnalysis(SentinelLog $log): void
    {
        if ($log->ai_status !== 'pending') {
            return;
        }

        $connection = config('sentinel.queue.connection') ?: config('queue.default');

        // "sync" would block the user's request, so run it after the response is sent.
        if ($connection === 'sync' && ! app()->runningInConsole()) {
            AnalyzeErrorJob::dispatchAfterResponse($log->id);
            return;
        }

        AnalyzeErrorJob::dispatch($log->id)
            ->onConnection(config('sentinel.queue.connection'))
            ->onQueue(config('sentinel.queue.name'));
    }
}
