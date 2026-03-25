@extends('admin.layout')
@section('title', 'Engagement · ' . $engagement->word_label)

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.shifu-engagements.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Engagements</a>
</div>

{{-- Header --}}
<div class="bg-white rounded-xl border border-gray-200 px-6 py-5 mb-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-3xl font-bold" style="font-family:'Noto Serif TC',serif">{{ $engagement->word_label }}</h1>
            <p class="text-sm text-gray-500 mt-1 font-mono">{{ $engagement->uuid }}</p>
        </div>
        <div class="text-right">
            @php
                $ctxLabel = match($engagement->context) {
                    'writing_conservatory' => 'Writing Conservatory',
                    'test' => 'Test',
                    'generation' => 'Generation',
                    default => $engagement->context,
                };
                $outcomeColor = match($engagement->outcome) {
                    'saved' => 'text-green-700',
                    'correct' => 'text-emerald-700',
                    'incorrect' => 'text-red-600',
                    default => 'text-gray-400',
                };
            @endphp
            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $ctxLabel }}</span>
            <p class="text-sm font-semibold {{ $outcomeColor }} mt-2">{{ $engagement->outcome ?? 'In progress' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-100 text-sm">
        <div>
            <p class="text-xs text-gray-500 uppercase">Learner</p>
            <p class="font-medium text-gray-900">{{ $engagement->user?->name ?? 'Guest' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase">Interactions</p>
            <p class="font-medium text-gray-900">{{ $engagement->interaction_count }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase">Started</p>
            <p class="font-medium text-gray-900">{{ $engagement->started_at->format('M j, Y g:i A') }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 uppercase">Completed</p>
            <p class="font-medium text-gray-900">{{ $engagement->completed_at?->format('M j, Y g:i A') ?? '---' }}</p>
        </div>
    </div>
</div>

{{-- Interaction Timeline --}}
<div class="space-y-4">
    @forelse ($engagement->interactions as $interaction)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {{-- Sequence header --}}
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Turn {{ $interaction->sequence }}</span>
                <div class="flex items-center gap-3">
                    @if ($interaction->is_correct === true)
                        <span class="text-xs font-medium text-green-700 bg-green-100 px-2 py-0.5 rounded-full">Correct</span>
                    @elseif ($interaction->is_correct === false)
                        <span class="text-xs font-medium text-red-600 bg-red-100 px-2 py-0.5 rounded-full">Incorrect</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $interaction->created_at->format('g:i:s A') }}</span>
                </div>
            </div>

            <div class="px-5 py-4 space-y-4">
                {{-- Learner input --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Learner</p>
                    <p class="text-lg" style="font-family:'Noto Serif TC',serif;line-height:1.8">{{ $interaction->learner_input }}</p>
                </div>

                {{-- Response --}}
                <div class="border-t border-gray-100 pt-3">
                    <p class="text-xs font-medium text-gray-500 uppercase mb-1">Response</p>
                    <p class="text-sm text-gray-800 leading-relaxed">{{ $interaction->shifu_response }}</p>
                </div>

                {{-- Hints --}}
                @if ($interaction->hints_used && count($interaction->hints_used))
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-1">Hints Used</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach ($interaction->hints_used as $hint)
                                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded">{{ $hint }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-200 px-6 py-8 text-center text-gray-400">
            No interactions recorded.
        </div>
    @endforelse
</div>

@endsection
