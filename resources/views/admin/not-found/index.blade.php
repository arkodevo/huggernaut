@extends('admin.layout')
@section('title', 'Not Found Words')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Not Found Words</h1>
    <p class="text-sm text-gray-500 mt-0.5">Characters and words learners searched for but aren't in the lexicon</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unique</p>
        <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($stats['unique_chars']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Occurrences</p>
        <p class="text-2xl font-bold text-indigo-700 mt-0.5">{{ number_format($stats['total_occurrences']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Trending 7d</p>
        <p class="text-2xl font-bold text-amber-600 mt-0.5">{{ number_format($stats['trending_7d']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</p>
        <p class="text-2xl font-bold text-orange-600 mt-0.5">{{ number_format($stats['pending']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Added</p>
        <p class="text-2xl font-bold text-green-700 mt-0.5">{{ number_format($stats['added']) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</p>
        <p class="text-2xl font-bold text-red-600 mt-0.5">{{ number_format($stats['rejected']) }}</p>
    </div>
</div>

{{-- Filters + Actions --}}
<div class="flex items-center justify-between mb-4">
<form method="GET" class="flex flex-wrap gap-3">
    <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <option value="">All Statuses</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="added" {{ request('status') === 'added' ? 'selected' : '' }}>Added</option>
        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
    </select>
    <select name="source" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <option value="">All Sources</option>
        <option value="search" {{ request('source') === 'search' ? 'selected' : '' }}>Search</option>
        <option value="import" {{ request('source') === 'import' ? 'selected' : '' }}>Import</option>
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm" title="From">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm" title="To">
    <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
    <a href="{{ route('admin.not-found.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Clear</a>
</form>
<div class="flex gap-2 items-center">
    <form method="POST" action="{{ route('admin.not-found.refresh') }}" class="inline">
        @csrf
        <button type="submit" class="bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-emerald-700">Refresh Status</button>
    </form>
    <a href="{{ route('admin.not-found.export', array_merge(request()->query(), ['format' => 'json'])) }}"
       class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200">Export JSON</a>
    <a href="{{ route('admin.not-found.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
       class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200">Export CSV</a>
</div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Character</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Occurrences</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Searchers</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Source</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">First Seen</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Last Seen</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($words as $word)
                @php
                    $statusColors = [
                        'pending'  => 'bg-orange-100 text-orange-800',
                        'added'    => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                    ];
                    $status = $word->gap_status ?? 'pending';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-2xl font-bold" style="font-family:'Noto Serif TC',serif">{{ $word->character }}</td>
                    <td class="px-4 py-3 text-center font-mono font-bold text-red-600">{{ $word->occurrences }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $word->unique_searchers }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $sources = explode(',', $word->sources ?? 'search'); @endphp
                        @foreach ($sources as $src)
                            <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $src === 'import' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">{{ $src }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$status] ?? 'bg-gray-100 text-gray-800' }}">{{ $status }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ \Carbon\Carbon::parse($word->first_seen)->diffForHumans() }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ \Carbon\Carbon::parse($word->last_seen)->diffForHumans() }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-2 justify-center">
                            <a href="{{ route('admin.not-found.show', $word->character) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Details</a>
                            @if ($status === 'pending')
                                <form method="POST" action="{{ route('admin.not-found.reject', $word->character) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Reject</button>
                                </form>
                            @elseif ($status === 'rejected')
                                <form method="POST" action="{{ route('admin.not-found.unreject', $word->character) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-amber-600 hover:text-amber-800 text-xs font-medium">Restore</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">No missing words recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $words->links() }}
</div>

@endsection
