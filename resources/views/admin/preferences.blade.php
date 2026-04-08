@extends('admin.layout')
@section('title', 'Preferences')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Preferences</h1>
    <p class="text-sm text-gray-500 mt-1">Personalise your admin editing experience.</p>
</div>

{{-- ── Display ─────────────────────────────────────────────────────────────── --}}
<section class="max-w-3xl bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h2 class="text-sm font-semibold text-gray-900 mb-1">Chinese Character Font</h2>
    <p class="text-xs text-gray-500 mb-5">
        Sets the typeface for all Chinese characters in the admin panel.
        The same three fonts will be offered to students on the learner-facing site.
    </p>

    @php
        $fonts = [
            [
                'key'     => 'biaukai',
                'name'    => '標楷體',
                'label'   => 'BiauKai — Regular Script',
                'stack'   => "BiauKai, STKaiti, KaiTi, serif",
                'note'    => 'Calligraphic strokes. Textbook standard in Taiwan.',
            ],
            [
                'key'     => 'noto-serif',
                'name'    => '宋體',
                'label'   => 'Noto Serif TC — Ming Style',
                'stack'   => "'Noto Serif TC', serif",
                'note'    => 'Elegant serifs. Traditional print and publishing.',
            ],
            [
                'key'     => 'noto-sans',
                'name'    => '黑體',
                'label'   => 'Noto Sans TC — Gothic',
                'stack'   => "'Noto Sans TC', sans-serif",
                'note'    => 'Clean strokes. Modern digital and signage use.',
            ],
        ];
    @endphp

    <div class="grid grid-cols-3 gap-4" x-data="{ selected: '{{ $currentFont }}' }">
        @foreach ($fonts as $font)
            <form method="POST" action="{{ route('admin.preferences.update') }}">
                @csrf
                <input type="hidden" name="chinese_font" value="{{ $font['key'] }}">
                <button type="submit"
                        class="w-full text-left rounded-xl border-2 p-4 transition-all
                               {{ $currentFont === $font['key']
                                   ? 'border-indigo-500 bg-indigo-50'
                                   : 'border-gray-200 hover:border-gray-300 bg-white' }}">

                    {{-- Preview --}}
                    <div class="text-4xl mb-3 leading-none"
                         style="font-family: {{ $font['stack'] }}">
                        永
                    </div>

                    {{-- Mixed preview line --}}
                    <p class="text-sm mb-3 text-gray-700"
                       style="font-family: {{ $font['stack'] }}">
                        「流動」Living Lexicon
                    </p>

                    {{-- Name + label --}}
                    <p class="text-xs font-semibold text-gray-900">{{ $font['name'] }}
                        <span class="font-normal text-gray-500">{{ $font['label'] }}</span>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $font['note'] }}</p>

                    {{-- Active indicator --}}
                    @if ($currentFont === $font['key'])
                        <span class="mt-2 inline-block text-xs font-medium text-indigo-600">✓ Active</span>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</section>

{{-- ── Verb Presentation ────────────────────────────────────────────────────── --}}
<section class="max-w-3xl bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h2 class="text-sm font-semibold text-gray-900 mb-1">Verb Presentation</h2>
    <p class="text-xs text-gray-500 mb-5">
        Controls how verb POS tags are displayed across the admin panel and word detail pages.
        Intricate shows the full TOCFL taxonomy; Consolidated collapses subtypes to three learner-friendly labels.
    </p>

    @php
        $verbModes = [
            [
                'key'      => 'intricate',
                'label'    => 'Intricate',
                'sublabel' => '精細',
                'note'     => 'Full TOCFL taxonomy — V, Vi, Vt, Vp, Vpt, Vs, Vst, Vsep, Vpsep, Vssep, Vaux…',
                'example'  => 'e.g. 離婚 → Vpsep · 喜歡 → Vst · 完成 → Vpt',
            ],
            [
                'key'      => 'consolidated',
                'label'    => 'Consolidated',
                'sublabel' => '簡化',
                'note'     => 'Three learner-friendly labels — transitive verb, intransitive verb, separable verb.',
                'example'  => 'e.g. 離婚 → separable · 喜歡 → transitive · 完成 → transitive',
            ],
        ];
    @endphp

    <div class="grid grid-cols-2 gap-4">
        @foreach ($verbModes as $mode)
            <form method="POST" action="{{ route('admin.preferences.update') }}">
                @csrf
                <input type="hidden" name="verb_presentation" value="{{ $mode['key'] }}">
                <button type="submit"
                        class="w-full text-left rounded-xl border-2 p-4 transition-all
                               {{ $verbPresentation === $mode['key']
                                   ? 'border-indigo-500 bg-indigo-50'
                                   : 'border-gray-200 hover:border-gray-300 bg-white' }}">

                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-base font-semibold text-gray-900">{{ $mode['label'] }}</span>
                        <span class="text-sm text-gray-400" style="font-family: BiauKai, STKaiti, KaiTi, serif">{{ $mode['sublabel'] }}</span>
                    </div>

                    <p class="text-xs text-gray-600 mb-2">{{ $mode['note'] }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $mode['example'] }}</p>

                    @if ($verbPresentation === $mode['key'])
                        <span class="mt-3 inline-block text-xs font-medium text-indigo-600">✓ Active</span>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</section>

{{-- ── Notes Coverage Languages ──────────────────────────────────────────────── --}}
<section class="max-w-3xl bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h2 class="text-sm font-semibold text-gray-900 mb-1">Notes Coverage Languages</h2>
    <p class="text-xs text-gray-500 mb-5">
        Select which languages have editorial coverage for formula, usage notes, and learner traps.
        Enabled languages will appear as editable fields in the sense editor.
    </p>

    <form method="POST" action="{{ route('admin.preferences.update') }}">
        @csrf
        <div class="space-y-2 mb-4">
            @foreach ($languages as $lang)
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="notes_coverage[]" value="{{ $lang->id }}"
                           class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                           {{ $lang->has_notes_coverage ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 font-medium">{{ $lang->name }}</span>
                    <span class="text-xs text-gray-400">({{ $lang->code }})</span>
                </label>
            @endforeach
        </div>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
            Save Coverage
        </button>
    </form>
</section>

{{-- Future preferences sections go here --}}

@endsection
