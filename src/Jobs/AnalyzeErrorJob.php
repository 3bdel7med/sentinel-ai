<?php

namespace Abdelhmed\SentinelAi\Jobs;

use Abdelhmed\SentinelAi\Exceptions\SentinelException;
use Abdelhmed\SentinelAi\Models\SentinelLog;
use Abdelhmed\SentinelAi\Services\GeminiClient;
use Abdelhmed\SentinelAi\Services\Redactor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class AnalyzeErrorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public int $logId)
    {
    }

    public function handle(GeminiClient $client, Redactor $redactor): void
    {
        $log = SentinelLog::find($this->logId);

        if (! $log) {
            return; // deleted while waiting in the queue
        }

        try {
            $log->update([
                'ai_analysis' => $client->analyze($log),
                'ai_status'   => 'done',
                'ai_error'    => null,
            ]);
        } catch (Throwable $e) {
            // Never rethrow: a failing analysis must not retry or be reported again.
            $message = $e instanceof SentinelException
                ? $e->getMessage()
                : 'Unexpected error while analyzing this error.';

            $log->update([
                'ai_status' => 'failed',
                'ai_error'  => Str::limit((string) $redactor->redact($message), 500),
            ]);
        }
    }
}
