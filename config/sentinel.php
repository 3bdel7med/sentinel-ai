<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    | Master switch. When false, nothing is captured and the dashboard routes
    | are not registered.
    */
    'enabled' => env('SENTINEL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    | Errors are sent to a third-party AI service, so production is NOT
    | included by default. Add 'production' only after you read the privacy
    | notes in the README.
    */
    'environments' => ['local', 'staging'],

    /*
    |--------------------------------------------------------------------------
    | Gemini
    |--------------------------------------------------------------------------
    */
    'gemini_api_key'    => env('GEMINI_API_KEY', ''),
    'model'             => env('SENTINEL_GEMINI_MODEL', 'gemini-3.6-flash'),
    'timeout'           => env('SENTINEL_TIMEOUT', 30),   // seconds
    'max_output_tokens' => 2048,

    // Language of the explanation (code and identifiers stay in English).
    'language' => env('SENTINEL_LANGUAGE', 'English'),

    /*
    |--------------------------------------------------------------------------
    | Analysis
    |--------------------------------------------------------------------------
    | auto_analyze: ask the AI to explain every NEW error automatically.
    | Tests are never generated automatically; use the button on the dashboard.
    */
    'auto_analyze' => env('SENTINEL_AUTO_ANALYZE', true),

    'queue' => [
        'connection' => env('SENTINEL_QUEUE_CONNECTION'),   // null = app default
        'name'       => env('SENTINEL_QUEUE'),              // null = default queue
    ],

    /*
    |--------------------------------------------------------------------------
    | Context sent to the AI
    |--------------------------------------------------------------------------
    */
    'snippet_radius' => 10,     // lines of code before/after the failing line
    'trace_limit'    => 4000,   // max characters of stack trace stored

    // Exception classes that should never be captured (instanceof check).
    'ignore' => [
        // \Illuminate\Http\Client\ConnectionException::class,
    ],

    // Extra regex patterns to redact before storing / sending anything.
    'redact_patterns' => [
        // '/\b\d{14,16}\b/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    | path:       URL prefix of the dashboard.
    | middleware: middleware group(s) applied to dashboard routes ('web' is required).
    | gate:       name of the Gate that decides who can open the dashboard.
    |             If the gate is not defined, only the 'local' environment is allowed.
    */
    'path'       => env('SENTINEL_PATH', 'sentinel'),
    'middleware' => ['web'],
    'gate'       => 'viewSentinel',

    // Used by `php artisan model:prune`. Schedule that command to enable it.
    'prune_after_days' => 30,
];
