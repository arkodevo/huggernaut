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

{{-- Future preferences sections go here --}}

@endsection
