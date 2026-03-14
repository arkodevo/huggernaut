@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-sm text-gray-500 mt-0.5">Phase 0 — word entry in progress</p>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-8">
    @php
        $statCards = [
            ['label' => 'Total Words',      'value' => $stats['words_total'],      'color' => 'text-gray-900'],
            ['label' => 'Published',        'value' => $stats['words_published'],  'color' => 'text-green-700'],
            ['label' => 'In Review',        'value' => $stats['words_review'],     'color' => 'text-amber-700'],
            ['label' => 'Draft',            'value' => $stats['words_draft'],      'color' => 'text-gray-500'],
            ['label' => 'Total Senses',     'value' => $stats['senses_total'],     'color' => 'text-indigo-700'],
            ['label' => 'Published Senses', 'value' => $stats['senses_published'], 'color' => 'text-green-700'],
        ];
    @endphp
    @foreach ($statCards as $card)
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $card['label'] }}</p>
            <p class="text-3xl font-bold {{ $card['color'] }} mt-1">{{ $card['value'] }}</p>
        </div>
    @endforeach
</div>

{{-- Recent words --}}
<div class="bg-white rounded-xl border border-gray-200">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Recently Added</h2>
        <a href="{{ route('admin.words.create') }}"
           class="text-xs font-medium text-indigo-600 hover:text-indigo-800">+ Add word</a>
    </div>

    @if ($recent->isEmpty())
        <p class="px-5 py-6 text-sm text-gray-400">No words yet. <a href="{{ route('admin.words.create') }}" class="text-indigo-600 hover:underline">Add the first one.</a></p>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach ($recent as $word)
                <li class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-4">
                        <span class="text-2xl">{{ $word->traditional }}</span>
                        <div>
                            <a href="{{ route('admin.words.show', $word) }}"
                               class="text-sm font-medium text-gray-900 hover:text-indigo-600">
                                {{ $word->smart_id }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $word->senses->count() }} sense(s)</p>
                        </div>
                    </div>
                    @include('admin.partials.status-badge', ['status' => $word->status])
                </li>
            @endforeach
        </ul>
    @endif
</div>

@endsection
