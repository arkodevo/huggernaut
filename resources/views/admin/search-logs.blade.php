@extends('admin.layout')
@section('title', 'Search Logs')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Search Logs</h1>
    <p class="text-sm text-gray-500 mt-0.5">Learner search queries and sentence analysis</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unique</p>
        <p class="text-2xl font-bold text-indigo-700 mt-0.5">{{ number_format($stats['unique_queries']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Zero Results</p>
        <p class="text-2xl font-bold text-red-600 mt-0.5">{{ number_format($stats['zero_results']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Sentences</p>
        <p class="text-2xl font-bold text-purple-700 mt-0.5">{{ number_format($stats['sentence_searches']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">With Not-Found</p>
        <p class="text-2xl font-bold text-amber-600 mt-0.5">{{ number_format($stats['with_not_found']) }}</p>
    </div>
</div>

{{-- Filters + Export --}}
<div class="flex items-center justify-between mb-4">
<form method="GET" class="flex flex-wrap gap-3">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Filter by query..."
           class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-48">
    <select name="search_type" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <option value="">All Types</option>
        <option value="word" {{ request('search_type') === 'word' ? 'selected' : '' }}>Word</option>
        <option value="sentence" {{ request('search_type') === 'sentence' ? 'selected' : '' }}>Sentence</option>
    </select>
    <select name="user_role" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <option value="">All Roles</option>
        <option value="learner" {{ request('user_role') === 'learner' ? 'selected' : '' }}>Learner</option>
        <option value="user" {{ request('user_role') === 'user' ? 'selected' : '' }}>Staff</option>
        <option value="admin" {{ request('user_role') === 'admin' ? 'selected' : '' }}>Admin</option>
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm" title="From">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm" title="To">
    <label class="flex items-center gap-1.5 text-sm text-gray-600">
        <input type="checkbox" name="zero_results" value="1" {{ request('zero_results') ? 'checked' : '' }}>
        Zero results
    </label>
    @if (request('session_id'))
        <input type="hidden" name="session_id" value="{{ request('session_id') }}">
        <span class="flex items-center gap-1 text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">
            Session: {{ substr(request('session_id'), 0, 8) }}...
            <a href="{{ route('admin.search-logs.index', request()->except('session_id')) }}" class="text-indigo-600 hover:text-indigo-900">&times;</a>
        </span>
    @endif
    <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
    <a href="{{ route('admin.search-logs.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Clear</a>
</form>
<div class="flex gap-2">
    <a href="{{ route('admin.search-logs.export', array_merge(request()->query(), ['format' => 'json'])) }}"
       class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200">Export JSON</a>
    <a href="{{ route('admin.search-logs.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
       class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200">Export CSV</a>
</div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Query</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Results</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Known</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Unknown</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Not Found</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Time</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-gray-900 max-w-xs truncate">{{ $log->query }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $log->user?->name ?? 'Guest' }}
                        @if ($log->user_role)
                            <span class="ml-1 px-1.5 py-0.5 rounded text-xs
                                {{ $log->user_role === 'learner' ? 'bg-green-100 text-green-700' :
                                   ($log->user_role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">{{ $log->user_role }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $log->search_type === 'sentence' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">{{ $log->search_type }}</span>
                    </td>
                    <td class="px-4 py-3 text-center {{ $log->results_count === 0 ? 'text-red-600 font-bold' : 'text-gray-600' }}">{{ $log->results_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $log->search_type === 'sentence' ? $log->known_count : '' }}</td>
                    <td class="px-4 py-3 text-center {{ $log->unknown_count > 0 ? 'text-amber-600 font-bold' : 'text-gray-600' }}">{{ $log->search_type === 'sentence' ? $log->unknown_count : '' }}</td>
                    <td class="px-4 py-3">
                        @if ($log->notFoundWords->count())
                            <div class="flex flex-wrap gap-1">
                                @foreach ($log->notFoundWords as $nf)
                                    <a href="{{ route('admin.not-found.show', $nf->character) }}"
                                       class="inline-block px-1.5 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded text-xs font-bold hover:bg-red-100"
                                       style="font-family:'Noto Serif TC',serif">{{ $nf->character }}</a>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">No search logs yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $logs->links() }}
</div>

@endsection
