<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentinel AI - Error Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="max-w-6xl mx-auto py-10 px-4">
        <!-- Header -->
        <!-- Header -->
<div class="flex justify-between items-center mb-8 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">🛡️ Sentinel AI Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Monitoring and analyzing application errors using artificial intelligence.</p>
    </div>
    
    <div class="flex items-center gap-3">
        <span class="bg-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1 rounded-full">Laravel Package</span>
        
        <form action="{{ url('sentinel/logs') }}" method="POST" onsubmit="return confirm('Are you sure to delete all logs');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-xs font-semibold px-3 py-2 rounded-lg transition">
                🗑️  Delete All
            </button>
        </form>
    </div>
</div>

        <!-- Logs List -->
        <div class="space-y-6">
            @forelse($logs as $log)
                <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-200 transition hover:shadow-md">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2">
                        <div>
                            <span class="inline-block bg-red-100 text-red-700 text-sm font-bold px-3 py-1 rounded-md mb-2">
                                {{ $log->error_message }}
                            </span>
                            <p class="text-xs text-gray-500 font-mono">
                                📁 {{ $log->file_path }} : <span class="text-red-500 font-bold">line {{ $log->line_number }}</span>
                            </p>
                        </div>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <!-- AI Analysis Box -->
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 mt-4">
                        <h3 class="font-bold text-slate-700 mb-2 flex items-center gap-2">
                            <span>🤖</span>   AI fixing :
                        </h3>
                        <div class="text-slate-600 text-sm leading-relaxed whitespace-pre-line font-mono bg-white p-4 rounded border border-slate-100">
                            {!! nl2br(e($log->ai_analysis)) !!}
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white text-center py-12 rounded-xl shadow-sm border border-gray-200">
                    <p class="text-gray-400 text-lg">Exellent , no Error </p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $logs->links() }}
        </div>
    </div>
</body>
</html>
