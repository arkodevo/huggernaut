@extends('admin.layout')
@section('title', 'Engagements')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Engagements</h1>
    <p class="text-sm text-gray-500 mt-0.5">All learner interactions with features</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    @php
        $statCards = [
            ['label' => 'Total',       'value' => $stats['total'],      'color' => 'text-gray-900'],
            ['label' => 'WC',          'value' => $stats['wc'],         'color' => 'text-indigo-700'],
            ['label' => 'Test',        'value' => $stats['test'],       'color' => 'text-purple-700'],
            ['label' => 'Generation',  'value' => $stats['generation'], 'color' => 'text-blue-700'],
            ['label' => 'Saved',       'value' => $stats['saved'],      'color' => 'text-green-700'],
            ['label' => 'Correct',     'value' => $stats['correct'],    'color' => 'text-emerald-700'],
            ['label' => 'Incorrect',   'value' => $stats['incorrect'],  'color' => 'text-red-600'],
        ];
    @endphp
    @foreach ($statCards as $card)
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $card['label'] }}</p>
            <p class="text-2xl font-bold {{ $card['color'] }} mt-0.5">{{ number_format($card['value']) }}</p>
        </div>
    @endforeach
</div>

{{-- Filters + Export --}}
<div class="flex items-center justify-between mb-4">
<form method="GET" class="flex flex-wrap gap-3">
    <select name="context" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <option value="">All Contexts</option>
        <option value="writing_conservatory" {{ request('context') === 'writing_conservatory' ? 'selected' : '' }}>Writing Conservatory</option>
        <option value="test" {{ request('context') === 'test' ? 'selected' : '' }}>Test</option>
        <option value="generation" {{ request('context') === 'generation' ? 'selected' : '' }}>Generation</option>
        <option value="analysis" {{ request('context') === 'analysis' ? 'selected' : '' }}>Analysis</option>
        <option value="chinese_names" {{ request('context') === 'chinese_names' ? 'selected' : '' }}>Chinese Names</option>
        <option value="enrichment" {{ request('context') === 'enrichment' ? 'selected' : '' }}>Enrichment</option>
    </select>
    <select name="outcome" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm">
        <option value="">All Outcomes</option>
        <option value="saved" {{ request('outcome') === 'saved' ? 'selected' : '' }}>Saved</option>
        <option value="correct" {{ request('outcome') === 'correct' ? 'selected' : '' }}>Correct</option>
        <option value="incorrect" {{ request('outcome') === 'incorrect' ? 'selected' : '' }}>Incorrect</option>
        <option value="abandoned" {{ request('outcome') === 'abandoned' ? 'selected' : '' }}>Abandoned</option>
    </select>
    <input type="text" name="word" value="{{ request('word') }}" placeholder="Word..."
           class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm w-32">
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm" title="From">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm" title="To">
    <button type="submit" class="bg-indigo-600 text-white px-4 py-1.5 rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
    <a href="{{ route('admin.shifu-engagements.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Clear</a>
</form>
<div class="flex gap-2 items-center">
    <a href="{{ route('admin.shifu-engagements.export', array_merge(request()->query(), ['format' => 'json'])) }}"
       class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200">Export JSON</a>
    <a href="{{ route('admin.shifu-engagements.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
       class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-200">Export CSV</a>
    <form method="POST" action="{{ route('admin.shifu-engagements.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
        @csrf
        <label class="bg-amber-100 text-amber-800 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-amber-200 cursor-pointer">
            Import Audit
            <input type="file" name="file" accept=".json" class="hidden" onchange="this.form.submit()">
        </label>
    </form>
</div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Word</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Context</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">Turns</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Outcome</th>
                <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase">Started</th>
                <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase">View</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($engagements as $e)
                @php
                    $ctxColors = [
                        'writing_conservatory' => 'bg-indigo-100 text-indigo-800',
                        'test'                 => 'bg-purple-100 text-purple-800',
                        'generation'           => 'bg-blue-100 text-blue-800',
                        'analysis'             => 'bg-teal-100 text-teal-800',
                        'chinese_names'        => 'bg-rose-100 text-rose-800',
                        'enrichment'           => 'bg-amber-100 text-amber-800',
                    ];
                    $outColors = [
                        'saved'     => 'text-green-700',
                        'correct'   => 'text-emerald-700',
                        'incorrect' => 'text-red-600',
                        'abandoned' => 'text-gray-500',
                        'rejected'  => 'text-red-500',
                        'partial'   => 'text-amber-600',
                    ];
                    $ctxLabel = match($e->context) {
                        'writing_conservatory' => 'WC',
                        'test' => 'Test',
                        'generation' => 'Gen',
                        'analysis' => 'Analysis',
                        'chinese_names' => 'Names',
                        'enrichment' => 'Enrich',
                        default => $e->context,
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-bold text-lg" style="font-family:'Noto Serif TC',serif">{{ $e->word_label }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $e->user?->name ?? 'Guest' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $ctxColors[$e->context] ?? 'bg-gray-100 text-gray-800' }}">{{ $ctxLabel }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-mono">{{ $e->interaction_count }}</td>
                    <td class="px-4 py-3 font-medium {{ $outColors[$e->outcome] ?? 'text-gray-400' }}">{{ $e->outcome ?? '...' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $e->started_at->diffForHumans() }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.shifu-engagements.show', $e->uuid) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No engagements yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $engagements->links() }}
</div>

@endsection
