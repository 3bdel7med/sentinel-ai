<?php

namespace Abdelhmed\SentinelAi\Services;

/**
 * Removes secrets and personal data from text BEFORE it is stored or sent to the AI.
 */
class Redactor
{
    public function redact(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        // Invalid UTF-8 would break json_encode() later.
        $text = mb_scrub($text);

        // The configured API key must never appear anywhere.
        $key = (string) config('sentinel.gemini_api_key');
        if ($key !== '') {
            $text = str_replace($key, '[REDACTED]', $text);
        }

        foreach ($this->patterns() as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        foreach ((array) config('sentinel.redact_patterns', []) as $pattern) {
            $text = @preg_replace($pattern, '[REDACTED]', $text) ?? $text;
        }

        return $text;
    }

    /** @return array<string, string> regex => replacement */
    protected function patterns(): array
    {
        return [
            // Emails
            '/\b[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}\b/' => '[email]',

            // Passwords inside connection strings: scheme://user:password@host
            '#(://[^\s:/@]+:)[^\s@/]+@#' => '$1[REDACTED]@',

            // Authorization headers
            '/\bBearer\s+[A-Za-z0-9\-._~+\/]+=*/i' => 'Bearer [REDACTED]',

            // Well-known token formats (Google, OpenAI-style, GitHub, Slack)
            '/\b(?:AIza[0-9A-Za-z_\-]{20,}|sk-[A-Za-z0-9_\-]{20,}|ghp_[A-Za-z0-9]{20,}|xox[baprs]-[A-Za-z0-9\-]{10,})/' => '[REDACTED]',

            // key = value / key: value / 'key' => 'value' for sensitive key names
            '/((?:password|passwd|pwd|secret|token|api[_\-]?key|access[_\-]?key|private[_\-]?key|authorization)["\']?\s*(?:=>|[:=])\s*["\']?)[^\s"\',;&)]+/i' => '$1[REDACTED]',
        ];
    }
}
