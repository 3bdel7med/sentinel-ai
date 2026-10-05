<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sentinel</title>
    @if ($hasPending)
        {{-- Keeps refreshing while the AI is still working on something. --}}
        <meta http-equiv="refresh" content="6">
    @endif
    <style>
        :root {
            --page: #edeff2; --surface: #ffffff; --ink: #161a22; --muted: #5f6877; --line: #d9dde4;
            --hit: #c23b2e; --hit-tint: #fbe9e6; --action: #136f63; --action-tint: #e1f1ee;
            --done: #8a93a1; --code: #f6f7f9;
            --sans: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --mono: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --page: #0e1217; --surface: #161b22; --ink: #e7eaee; --muted: #98a1af; --line: #2a313b;
                --hit: #ff7b6e; --hit-tint: #3a1d1a; --action: #4db6a5; --action-tint: #16312d;
                --done: #6b7482; --code: #0f1318;
            }
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--page); color: var(--ink); font: 15px/1.55 var(--sans); }
        a { color: var(--action); }
        .wrap { max-width: 980px; margin: 0 auto; padding: 32px 16px 64px; }

        .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        h1 { font-size: 24px; margin: 0; letter-spacing: -.01em; }
        .sub { margin: 2px 0 0; color: var(--muted); font-size: 14px; }
        .top-actions { display: flex; gap: 8px; flex-wrap: wrap; }

        .btn { font: inherit; font-size: 13px; cursor: pointer; border: 1px solid var(--line); background: var(--surface);
               color: var(--ink); border-radius: 7px; padding: 5px 11px; }
        .btn:hover { border-color: var(--muted); }
        .btn:focus-visible, .tabs a:focus-visible, input:focus-visible { outline: 2px solid var(--action); outline-offset: 2px; }
        .btn.primary { background: var(--action); border-color: var(--action); color: #fff; }
        .btn.danger { color: var(--hit); border-color: var(--hit); background: transparent; }
        form { margin: 0; }

        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
        .tabs { display: flex; gap: 4px; background: var(--surface); border: 1px solid var(--line); border-radius: 9px; padding: 3px; }
        .tabs a { padding: 5px 12px; border-radius: 6px; color: var(--muted); text-decoration: none; font-size: 14px; }
        .tabs a.on { background: var(--ink); color: var(--surface); }
        .tabs b { margin-left: 6px; font-weight: 600; }
        .search { display: flex; gap: 6px; }
        .search input { font: inherit; font-size: 14px; border: 1px solid var(--line); background: var(--surface); color: var(--ink);
                        border-radius: 7px; padding: 6px 10px; width: 240px; max-width: 100%; }

        .banner { border: 1px solid var(--line); background: var(--surface); border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; font-size: 14px; }
        .banner.err { border-color: var(--hit); background: var(--hit-tint); }

        .list { background: var(--surface); border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
        .item { border-top: 1px solid var(--line); border-left: 4px solid var(--hit); }
        .item:first-child { border-top: 0; }
        .item.done { border-left-color: var(--done); }
        .item > summary { list-style: none; cursor: pointer; padding: 14px 16px; display: grid; grid-template-columns: 1fr auto; gap: 2px 20px; }
        .item > summary::-webkit-details-marker { display: none; }
        .item > summary:hover { background: var(--code); }
        .item > summary:focus-visible { outline: 2px solid var(--action); outline-offset: -2px; }
        .cls { font-weight: 600; grid-column: 1; }
        .msg, .where { grid-column: 1; font-family: var(--mono); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .msg { font-size: 13px; }
        .where { font-size: 12.5px; color: var(--muted); }
        .side { grid-column: 2; grid-row: 1 / span 3; display: flex; flex-direction: column; align-items: flex-end; gap: 2px;
                font-size: 13px; color: var(--muted); text-align: right; }
        .count { color: var(--hit); font-weight: 600; }
        .item.done .count { color: var(--muted); }
        .state-fail { color: var(--hit); }

        .body { padding: 4px 16px 18px 16px; display: grid; gap: 22px; }
        .facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px 20px; margin: 0; font-size: 13px; }
        .facts dt { color: var(--muted); }
        .facts dd { margin: 0; overflow-wrap: anywhere; }
        .sec-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 8px; }
        h3 { font-size: 14px; margin: 0; }
        .muted { color: var(--muted); font-size: 14px; margin: 0; }
        .fail { color: var(--hit); font-size: 14px; margin: 0; }

        .snip { background: var(--code); border: 1px solid var(--line); border-radius: 8px; overflow-x: auto; padding: 6px 0;
                font: 12.5px/1.65 var(--mono); }
        .ln { display: flex; white-space: pre; min-width: max-content; }
        .ln .no { flex: 0 0 56px; padding-right: 14px; text-align: right; color: var(--muted); user-select: none; }
        .ln code { font: inherit; padding-right: 16px; min-height: 1.65em; }
        .ln.hit { background: var(--hit-tint); box-shadow: inset 3px 0 0 var(--hit); }
        .ln.hit .no { color: var(--hit); font-weight: 700; }

        .md { overflow-wrap: anywhere; }
        .md > :first-child { margin-top: 0; }
        .md h2 { font-size: 15px; margin: 16px 0 4px; }
        .md p, .md ul, .md ol { margin: 6px 0; }
        .md code { font-family: var(--mono); font-size: .92em; background: var(--code); border-radius: 4px; padding: 1px 4px; }
        .md pre { position: relative; direction: ltr; text-align: left; background: var(--code); border: 1px solid var(--line);
                  border-radius: 8px; padding: 12px 14px; overflow-x: auto; font: 12.5px/1.6 var(--mono); }
        .md pre code { background: none; padding: 0; font-size: inherit; }
        .md pre .copy { position: absolute; top: 6px; right: 6px; }

        details.trace > summary { cursor: pointer; font-size: 14px; font-weight: 600; }
        details.trace pre { margin: 8px 0 0; background: var(--code); border: 1px solid var(--line); border-radius: 8px;
                            padding: 12px 14px; overflow-x: auto; font: 12px/1.6 var(--mono); }
        .row-actions { display: flex; gap: 8px; padding-top: 4px; border-top: 1px solid var(--line); padding-top: 14px; }

        .empty { padding: 44px 20px; text-align: center; color: var(--muted); }
        .empty strong { display: block; color: var(--ink); font-size: 16px; margin-bottom: 4px; }
        .pager { display: flex; justify-content: center; align-items: center; gap: 18px; margin-top: 18px; font-size: 14px; color: var(--muted); }

        @media (max-width: 640px) {
            .item > summary { grid-template-columns: 1fr; }
            .side { grid-column: 1; grid-row: auto; flex-direction: row; gap: 12px; align-items: baseline; text-align: left; margin-top: 4px; }
            .search input { width: 100%; }
            .search { width: 100%; }
        }
    </style>
</head>
<body>
<div class="wrap">

    <header class="top">
        <div>
            <h1>Sentinel</h1>
            <p class="sub">Exceptions from this app, explained by AI.</p>
        </div>
        <div class="top-actions">
            @if ($counts['resolved'] > 0)
                <form method="POST" action="{{ route('sentinel.clear', ['scope' => 'resolved']) }}"
                      onsubmit="return confirm('Delete all resolved errors?');">
                    @csrf @method('DELETE')
                    <button class="btn">Clear resolved</button>
                </form>
            @endif
            @if ($counts['open'] + $counts['resolved'] > 0)
                <form method="POST" action="{{ route('sentinel.clear') }}"
                      onsubmit="return confirm('Delete ALL errors? This cannot be undone.');">
                    @csrf @method('DELETE')
                    <button class="btn danger">Delete all</button>
                </form>
            @endif
        </div>
    </header>

    @if (session('sentinel_error'))
        <div class="banner err" role="alert">{{ session('sentinel_error') }}</div>
    @endif
    @unless ($hasKey)
        <div class="banner">GEMINI_API_KEY is not set. Errors are still listed, without AI analysis.</div>
    @endunless

    <div class="toolbar">
        <nav class="tabs" aria-label="Filter errors">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'open', 'page' => null]) }}" class="{{ $status === 'open' ? 'on' : '' }}">Open<b>{{ $counts['open'] }}</b></a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'resolved', 'page' => null]) }}" class="{{ $status === 'resolved' ? 'on' : '' }}">Resolved<b>{{ $counts['resolved'] }}</b></a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => null]) }}" class="{{ $status === 'all' ? 'on' : '' }}">All</a>
        </nav>
        <form class="search" method="GET" action="{{ route('sentinel.index') }}" role="search">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search message, class or file" aria-label="Search errors">
            <button class="btn">Search</button>
        </form>
    </div>

    <div class="list">
        @forelse ($logs as $log)
            <details class="item {{ $log->is_resolved ? 'done' : '' }}" data-id="{{ $log->id }}">
                <summary>
                    <span class="cls">{{ $log->short_class }}</span>
                    <span class="msg">{{ $log->message }}</span>
                    <span class="where">{{ $log->short_file }}:{{ $log->line }}</span>
                    <span class="side">
                        @if ($log->occurrences > 1)
                            <span class="count">{{ number_format($log->occurrences) }} times</span>
                        @endif
                        <span>{{ ($log->last_seen_at ?? $log->created_at)->diffForHumans() }}</span>
                        @if ($log->is_resolved)
                            <span>Resolved</span>
                        @elseif ($log->ai_status === 'pending')
                            <span>Analyzing</span>
                        @elseif ($log->ai_status === 'failed')
                            <span class="state-fail">Analysis failed</span>
                        @endif
                    </span>
                </summary>

                <div class="body">
                    <dl class="facts">
                        <div><dt>Exception</dt><dd>{{ $log->exception_class }}</dd></div>
                        @if ($log->method && $log->url)
                            <div><dt>Request</dt><dd>{{ $log->method }} {{ parse_url($log->url, PHP_URL_PATH) ?: '/' }}</dd></div>
                        @endif
                        <div><dt>First seen</dt><dd>{{ $log->created_at->diffForHumans() }}</dd></div>
                        @if ($log->environment)
                            <div><dt>Environment</dt><dd>{{ $log->environment }}</dd></div>
                        @endif
                    </dl>

                    @if (! empty($log->code_snippet))
                        <section>
                            <div class="sec-head"><h3>Code</h3><span class="muted">{{ $log->short_file }}</span></div>
                            <div class="snip" tabindex="0">
                                @foreach ($log->code_snippet as $number => $code)
                                    <div class="ln {{ (int) $number === $log->line ? 'hit' : '' }}"><span class="no">{{ $number }}</span><code>{{ $code }}</code></div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section>
                        <div class="sec-head">
                            <h3>AI analysis</h3>
                            @if ($hasKey)
                                <form method="POST" action="{{ route('sentinel.analyze', $log) }}">
                                    @csrf
                                    <button class="btn">{{ $log->ai_status === 'done' ? 'Analyze again' : 'Analyze' }}</button>
                                </form>
                            @endif
                        </div>
                        @if ($log->ai_status === 'done')
                            <div class="md" dir="auto">{!! $log->analysis_html !!}</div>
                        @elseif ($log->ai_status === 'pending')
                            <p class="muted">Analyzing. This page refreshes on its own. If it stays like this, start a queue worker with <code>php artisan queue:work</code>.</p>
                        @elseif ($log->ai_status === 'failed')
                            <p class="fail">{{ $log->ai_error ?: 'The analysis failed.' }}</p>
                        @else
                            <p class="muted">{{ $hasKey ? 'This error has not been analyzed yet.' : 'Set GEMINI_API_KEY in your .env to get an explanation.' }}</p>
                        @endif
                    </section>

                    <section>
                        <div class="sec-head">
                            <h3>Test</h3>
                            @if ($hasKey)
                                <form method="POST" action="{{ route('sentinel.test', $log) }}">
                                    @csrf
                                    <button class="btn primary">{{ $log->ai_test ? 'Generate again' : 'Generate test' }}</button>
                                </form>
                            @endif
                        </div>
                        @if ($log->ai_test)
                            <div class="md" dir="auto">{!! $log->test_html !!}</div>
                        @else
                            <p class="muted">Generate a regression test for this error when you need one.</p>
                        @endif
                    </section>

                    @if ($log->trace)
                        <details class="trace">
                            <summary>Stack trace</summary>
                            <pre>{{ $log->trace }}</pre>
                        </details>
                    @endif

                    <div class="row-actions">
                        <form method="POST" action="{{ route('sentinel.resolve', $log) }}">
                            @csrf @method('PATCH')
                            <button class="btn">{{ $log->is_resolved ? 'Reopen' : 'Mark as resolved' }}</button>
                        </form>
                        <form method="POST" action="{{ route('sentinel.destroy', $log) }}"
                              onsubmit="return confirm('Delete this error?');">
                            @csrf @method('DELETE')
                            <button class="btn danger">Delete</button>
                        </form>
                    </div>
                </div>
            </details>
        @empty
            <div class="empty">
                @if ($search !== '')
                    <strong>No errors match “{{ $search }}”.</strong>
                    Try a class name, part of the message or a file name.
                @elseif ($status === 'resolved')
                    <strong>Nothing resolved yet.</strong>
                    Errors you mark as resolved are kept here.
                @else
                    <strong>No errors to show.</strong>
                    New exceptions appear here as soon as your app reports them.
                @endif
            </div>
        @endforelse
    </div>

    @if ($logs->hasPages())
        <nav class="pager" aria-label="Pagination">
            @if ($logs->onFirstPage()) <span>Newer</span> @else <a href="{{ $logs->previousPageUrl() }}">Newer</a> @endif
            <span>Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
            @if ($logs->hasMorePages()) <a href="{{ $logs->nextPageUrl() }}">Older</a> @else <span>Older</span> @endif
        </nav>
    @endif
</div>

<script>
(function () {
    var KEY = 'sentinel-open';
    function read() { try { return JSON.parse(sessionStorage.getItem(KEY) || '[]'); } catch (e) { return []; } }
    function write(v) { try { sessionStorage.setItem(KEY, JSON.stringify(v)); } catch (e) {} }

    // Keep expanded rows open after an action or an auto refresh.
    var opened = read();
    document.querySelectorAll('details.item').forEach(function (d) {
        if (opened.indexOf(d.dataset.id) > -1) { d.open = true; }
        d.addEventListener('toggle', function () {
            var s = read(), i = s.indexOf(d.dataset.id);
            if (d.open && i < 0) { s.push(d.dataset.id); }
            if (!d.open && i > -1) { s.splice(i, 1); }
            write(s);
        });
    });

    // Copy button on every code block of the AI answer.
    if (!navigator.clipboard) { return; }
    document.querySelectorAll('.md pre').forEach(function (pre) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'btn copy'; b.textContent = 'Copy';
        b.addEventListener('click', function () {
            var code = pre.querySelector('code') || pre;
            navigator.clipboard.writeText(code.innerText).then(function () {
                b.textContent = 'Copied';
                setTimeout(function () { b.textContent = 'Copy'; }, 1500);
            });
        });
        pre.appendChild(b);
    });
})();
</script>
</body>
</html>
