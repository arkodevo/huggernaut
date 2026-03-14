@extends('admin.layout')
@section('title', 'Edit Sense — ' . $word->traditional)

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.words.show', $word) }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ $word->traditional }}</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Sense</h1>
</div>

@include('admin.senses._form', [
    'action'       => route('admin.words.senses.update', [$word, $sense]),
    'method'       => 'PUT',
    'sense'        => $sense,
    'word'         => $word,
    'attributes'   => $attributes,
    'posLabels'    => $posLabels,
    'languages'    => $languages,
    'existingDefs' => $sense->definitions->map(fn ($d) => [
        'id'              => $d->id,
        'language_id'     => $d->language_id,
        'pos_id'          => $d->pos_id,
        'definition_text' => $d->definition_text,
        'sort_order'      => $d->sort_order,
    ])->values()->all(),
])

{{-- ── Examples section (edit only) ──────────────────────────────────── --}}
<div class="mt-6 bg-white rounded-xl border border-gray-200">
    <div class="px-5 py-3 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Examples ({{ $sense->examples->count() }})</h2>
    </div>

    @if ($sense->examples->isEmpty())
        <p class="px-5 py-4 text-sm text-gray-400">No examples yet.</p>
    @else
        @foreach ($sense->examples as $ex)
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="text-sm text-gray-900">{{ $ex->chinese_text }}</p>
                @if ($ex->english_text)
                    <p class="text-xs text-gray-500 mt-0.5">{{ $ex->english_text }}</p>
                @endif
                <div class="flex gap-3 mt-2">
                    <span class="text-xs text-gray-400">{{ $ex->source }}</span>
                    @if ($ex->is_suppressed)
                        <span class="text-xs text-red-500">suppressed</span>
                    @endif
                    <form method="POST" action="{{ route('admin.examples.destroy', $ex) }}"
                          onsubmit="return confirm('Delete this example?')" class="ml-auto">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    {{-- Add example --}}
    <div class="px-5 py-4 bg-gray-50 rounded-b-xl">
        <p class="text-xs font-medium text-gray-600 mb-3">Add example</p>
        <form method="POST" action="{{ route('admin.senses.examples.store', $sense) }}" class="space-y-3">
            @csrf
            <textarea name="chinese_text" rows="2" placeholder="Chinese text…" required
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
            <textarea name="english_text" rows="1" placeholder="English translation (optional)"
                      class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"></textarea>
            <div class="flex gap-3 items-center">
                <select name="source"
                        class="rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @foreach (['default', 'ai_generated', 'student', 'community'] as $s)
                        <option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-3 py-1.5 rounded-lg bg-gray-800 text-xs font-medium text-white hover:bg-gray-700 transition-colors">
                    Add
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
