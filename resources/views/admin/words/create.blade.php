@extends('admin.layout')
@section('title', 'Add Word')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.words.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Words</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">Add Word Object</h1>
    <p class="text-sm text-gray-500">The orthographic identity — pronunciation and senses are added next.</p>
</div>

<div class="max-w-2xl bg-white rounded-xl border border-gray-200 p-6">
    <form method="POST" action="{{ route('admin.words.store') }}" class="space-y-5">
        @csrf

        {{-- Traditional --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Traditional character(s) <span class="text-red-500">*</span></label>
            <input name="traditional" value="{{ old('traditional') }}" required
                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('traditional') border-red-400 @enderror"
                   placeholder="e.g. 行">
            @error('traditional')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Simplified --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Simplified (if different)</label>
            <input name="simplified" value="{{ old('simplified') }}"
                   class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                   placeholder="Leave blank if identical">
        </div>

        {{-- Radical --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kangxi Radical <span class="text-red-500">*</span></label>
            <select name="radical_id" required
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('radical_id') border-red-400 @enderror">
                <option value="">Select radical…</option>
                @foreach ($radicals as $radical)
                    <option value="{{ $radical->id }}" {{ old('radical_id') == $radical->id ? 'selected' : '' }}>
                        {{ $radical->id }}. {{ $radical->character }} — {{ $radical->meaning_en }} ({{ $radical->stroke_count }} strokes)
                    </option>
                @endforeach
            </select>
            @error('radical_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Strokes --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Strokes (traditional) <span class="text-red-500">*</span></label>
                <input name="strokes_trad" type="number" min="1" max="64" value="{{ old('strokes_trad') }}" required
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                @error('strokes_trad')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Strokes (simplified)</label>
                <input name="strokes_simp" type="number" min="1" max="64" value="{{ old('strokes_simp') }}"
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
        </div>

        {{-- Structure --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Character Structure</label>
            <select name="structure"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="">Unknown / not set</option>
                @foreach (['single', 'left-right', 'top-bottom', 'enclosing'] as $s)
                    <option value="{{ $s }}" {{ old('structure') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('-', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                @foreach (['draft', 'review', 'published'] as $s)
                    <option value="{{ $s }}" {{ old('status', 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                Create word
            </button>
            <a href="{{ route('admin.words.index') }}"
               class="px-5 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

@endsection
