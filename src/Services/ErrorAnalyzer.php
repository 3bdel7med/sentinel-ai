<?php
namespace Abdelhmed\SentinelAi\Services;

use Illuminate\Support\Facades\Http;
use Abdelhmed\SentinelAi\Models\SentinelLog;
use Throwable;

class ErrorAnalyzer
{
    public function handle(Throwable $e)
    {
        $apiKey = config('sentinel.gemini_api_key');
        
        if (empty($apiKey)) {
            \Log::warning("Sentinel AI: API Key is missing!");
            return;
        }

        $errorMessage = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        $prompt = "You are an expert Laravel developer. An exception occurred:\n" .
                  "Error: {$errorMessage}\n" .
                  "File: {$file} on line {$line}\n" .
                  "Provide the fix in Arabic and English concisely.";

        $aiAnalysis = "Unable to analyze.";

        try {
            $response = Http::timeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $aiAnalysis = $response->json('candidates.0.content.parts.0.text');
                \Log::info("🚀 Sentinel AI Success Response");
            } else {
                $aiAnalysis = "API Failed: " . $response->body();
                \Log::error("❌ Sentinel AI API Failed: " . $response->body());
            }

        } catch (\Exception $ex) {
            $aiAnalysis = "Connection Exception: " . $ex->getMessage();
            \Log::error("🔥 Sentinel AI Connection Exception: " . $ex->getMessage());
        }

        // حفظ الخطأ وتحليل الذكاء الاصطناعي في جدول الباكيدج تلقائياً
        try {
            SentinelLog::create([
                'error_message' => $errorMessage,
                'file_path'     => $file,
                'line_number'   => $line,
                'ai_analysis'   => $aiAnalysis,
            ]);
        } catch (\Exception $dbEx) {
            \Log::error("Sentinel DB Error: " . $dbEx->getMessage());
        }
    }
}