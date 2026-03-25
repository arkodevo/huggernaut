@extends('admin.layout')
@section('title', 'Not Found · ' . $character)

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.not-found.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Not Found</a>
</div>

{{-- Header --}}
<div class="bg-white rounded-xl border border-gray-200 px-6 py-5 mb-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-5xl font-bold" style="font-family:'Noto Serif TC',serif">{{ $character }}</h1>
            <p class="text-sm text-gray-500 mt-2">Not yet in the lexicon</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 uppercase">Priority Signal</p>
            <p class="text-3xl font-bold text-red-600 mt-1">{{ number_format($stats['total_searches']) }}</p>
            <p class="text-xs text-gray-500">total searches</p>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100 text-sm">
        <div>
            <p class="text-xs text-gray-500 uppercase">Total Searches</p>
            <p class="font-medium text-gray-900">{{ $stats['total_searches'] }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase">Unique Searchers</p>
            <p class="font-medium text-gray-900">{{ $stats['unique_searchers'] }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase">First Seen</p>
            <p class="font-medium text-gray-900">{{ $stats['first_seen'] ? \Carbon\Carbon::parse($stats['first_seen'])->format('M j, Y') : '---' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase">Last Seen</p>
            <p class="font-medium text-gray-900">{{ $stats['last_seen'] ? \Carbon\Carbon::parse($stats['last_seen'])->format('M j, Y') : '---' }}</p>
        </div>
    </div>
</div>

{{-- Search Logs --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
        <h2 class="text-sm font-semibold text-gray-900">Search Logs containing "{{ $character }}"</h2>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Query</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Role</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Session</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Time</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-gray-900">{{ $log->query }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $log->user?->name ?? 'Guest' }}</td>
                    <td class="px-4 py-3">
                        @if ($log->user_role)
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $log->user_role === 'learner' ? 'bg-green-100 text-green-800' :
                                   ($log->user_role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">{{ $log->user_role }}</span>
                        @else
                            <span class="text-gray-400">guest</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono">
                        @if ($log->session_id)
                            <a href="{{ route('admin.search-logs.index', ['session_id' => $log->session_id]) }}"
                               class="text-indigo-600 hover:text-indigo-800">{{ substr($log->session_id, 0, 8) }}...</a>
                        @else
                            ---
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">No search logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $logs->links() }}
</div>

@endsection
