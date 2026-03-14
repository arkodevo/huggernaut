@extends('admin.layout')
@section('title', 'Attribute Settings')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Attribute Settings</h1>
        <p class="text-sm text-gray-500 mt-1">
            Set the minimum learner band at which each filter attribute appears by default.
            <span class="font-medium">0 = always visible.</span>
            Learners can override these defaults at any time.
        </p>
    </div>
</div>

@foreach ($categories as $category)
    @php $catLabel = $category->labels->first()?->label ?? $category->slug; @endphp

    <div class="mb-6">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2 px-1">
            {{ $catLabel }}
        </h2>

        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @foreach ($category->attributes as $attribute)
                @php $attrLabel = $attribute->labels->first()?->label ?? $attribute->slug; @endphp

                <div class="flex items-center gap-4 px-5 py-3">

                    {{-- Attribute name --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ $attrLabel }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $attribute->slug }}</p>
                    </div>

                    {{-- Flags --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($attribute->is_multi_select)
                            <span class="text-xs bg-blue-50 text-blue-600 border border-blue-200 rounded px-1.5 py-0.5">multi</span>
                        @endif
                        @if ($attribute->is_spectrum)
                            <span class="text-xs bg-purple-50 text-purple-600 border border-purple-200 rounded px-1.5 py-0.5">spectrum</span>
                        @endif
                    </div>

                    {{-- learner_min_band picker --}}
                    <form method="POST"
                          action="{{ route('admin.attribute-settings.update', $attribute) }}"
                          class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')

                        <select name="learner_min_band"
                                onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                            <option value="0" {{ $attribute->learner_min_band === 0 ? 'selected' : '' }}>
                                Always visible
                            </option>
                            @foreach (['Prep', 'Entry', 'Basic', 'Advanced', 'High', 'Fluency'] as $i => $band)
                                <option value="{{ $i + 1 }}" {{ $attribute->learner_min_band === ($i + 1) ? 'selected' : '' }}>
                                    Band {{ $i + 1 }} — {{ $band }}
                                </option>
                            @endforeach
                        </select>

                        <noscript>
                            <button type="submit" class="px-2 py-1 text-xs bg-gray-800 text-white rounded">Save</button>
                        </noscript>
                    </form>

                </div>
            @endforeach
        </div>
    </div>
@endforeach

@endsection
