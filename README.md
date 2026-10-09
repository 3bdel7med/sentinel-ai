# Sentinel AI 🛡️

Captures the exceptions of your Laravel app, explains them with Google Gemini, and shows everything on a clean dashboard. You can mark errors as resolved, delete them, and generate a regression test on demand.

[https://github.com/user-attachments/assets/8d44a897-2318-4e96-a3c8-fe8bf8f5082c]



## Features

* 📊 **Smart Error Aggregation:** Groups identical exceptions and tracks occurrence count to prevent log clutter.
* 🤖 **AI-Powered Diagnostics:** Uses Google Gemini API to analyze stack traces and suggest exact fixes.
* 🧪 **Automated Pest Tests:** Auto-generates ready-to-run Pest PHP test cases for captured exceptions.
* 🔒 **Built-in Security:** Secured via Gate authorization or local environment fallbacks.

## Requirements

* PHP 8.0+
* Laravel 10, 11, or 12

## Install

```bash
composer require abdelhmed/sentinel-ai
php artisan migrate

```

Add your key to `.env`:

```env
GEMINI_API_KEY=your-key

```

Open `/sentinel` in your browser.

## Who can open the dashboard

Same model as Telescope. Define the `viewSentinel` gate (for example in `AppServiceProvider`):

```php
Gate::define('viewSentinel', fn (?User $user) => in_array($user?->email, ['you@company.com']));

```

If the gate is **not** defined, the dashboard is only available in the `local` environment (403 everywhere else).

## Environments and privacy

Error data is sent to Google Gemini, so by default only `local` and `staging` are captured (`config('sentinel.environments')`). Add `production` deliberately.

Before anything is stored or sent, Sentinel redacts emails, bearer tokens, API keys, passwords in `key=value` pairs and in connection strings. Query strings of URLs are never stored. Add your own patterns in `redact_patterns`. Redaction is best effort, not a guarantee.

## Queue

New errors are analyzed in a queued job. With the `sync` driver the job runs after the response is sent. For production-like setups run a worker: `php artisan queue:work`.
Use `SENTINEL_QUEUE_CONNECTION` and `SENTINEL_QUEUE` to isolate it.

## How it works

* One row per unique error (exception class + file + line). Repeats only increase `occurrences`. A resolved error that happens again is re-opened.
* The AI receives the exception, the code around the failing line, the top of the trace and the request path. Tests are generated only when you click **Generate test**.

## Cleanup

Old errors are pruned by Laravel's prune command. Schedule it:

```php
Schedule::command('model:prune', ['--model' => [\Abdelhmed\SentinelAi\Models\SentinelLog::class]])->daily();

```

`prune_after_days` (default 30) is in the config.

## Publish files

```bash
php artisan vendor:publish --tag=sentinel-config
php artisan vendor:publish --tag=sentinel-views

```

## License

MIT
