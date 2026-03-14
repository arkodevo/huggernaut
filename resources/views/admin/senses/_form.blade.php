@php
    $v = fn(string $field, $default = null) => old($field, $sense?->{$field} ?? $default);
    $selectedDesignations = $sense?->designations->pluck('id')->toArray() ?? [];
    $singleSelectAttrs = ['channel', 'connotation', 'semantic-mode', 'sensitivity', 'domain', 'tocfl-level', 'hsk-level'];

    // Build the initial definitions array for Alpine
    $initDefs = ! empty($existingDefs) ? $existingDefs : [[
        'id' => null, 'language_id' => '', 'pos_id' => '', 'definition_text' => '', 'sort_order' => 0,
    ]];
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6 max-w-3xl">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    {{-- Validation summary --}}
    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3">
            <p class="text-sm font-medium text-red-800 mb-1">Please fix the following:</p>
            <ul class="text-sm text-red-700 list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Pronunciation ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Reading</h3>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pronunciation <span class="text-red-500">*</span></label>
                <select name="pronunciation_id" required
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('pronunciation_id') border-red-400 @enderror">
                    <option value="">Select pronunciation…</option>
                    @foreach ($word->pronunciations as $pron)
                        <option value="{{ $pron->id }}"
                            {{ $v('pronunciation_id', $sense?->pronunciation_id) == $pron->id ? 'selected' : '' }}>
                            {{ $pron->pronunciation_text }} ({{ $pron->pronunciationSystem->name }})
                        </option>
                    @endforeach
                </select>
                @if ($word->pronunciations->isEmpty())
                    <p class="mt-1 text-xs text-amber-600">⚠ Add a pronunciation to the word first.</p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @foreach (['draft', 'review', 'published'] as $s)
                        <option value="{{ $s }}" {{ $v('status', 'draft') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Spectrum designations (single-select) ────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Attributes — Single Select</h3>
        <div class="grid grid-cols-2 gap-4">
            @foreach ($singleSelectAttrs as $slug)
                @php
                    $attr = $attributes[$slug] ?? null;
                    if (! $attr) continue;
                    // Map slug to model field name
                    $fieldMap = [
                        'channel'       => 'channel_id',
                        'connotation'   => 'connotation_id',
                        'semantic-mode' => 'semantic_mode_id',
                        'sensitivity'   => 'sensitivity_id',
                        'domain'        => 'domain_id',
                        'tocfl-level'   => 'tocfl_level_id',
                        'hsk-level'     => 'hsk_level_id',
                    ];
                    $field = $fieldMap[$slug];
                    $current = $v($field);
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $attr->labels->first()?->label ?? $attr->slug }}</label>
                    <select name="{{ $field }}"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">— not set —</option>
                        @foreach ($attr->designations as $des)
                            <option value="{{ $des->id }}" {{ $current == $des->id ? 'selected' : '' }}>
                                {{ $des->labels->first()?->label ?? $des->slug }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Multi-select designations (register, dimension) ─────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Attributes — Multi-Select</h3>
        <div class="grid grid-cols-2 gap-6">
            @foreach (['register', 'dimension'] as $slug)
                @php $attr = $attributes[$slug] ?? null; @endphp
                @if ($attr)
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ $attr->labels->first()?->label ?? $attr->slug }}</p>
                        <div class="space-y-1.5">
                            @foreach ($attr->designations as $des)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="designations[]" value="{{ $des->id }}"
                                           class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                                           {{ in_array($des->id, old('designations', $selectedDesignations)) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">{{ $des->labels->first()?->label ?? $des->slug }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Scalar attributes ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Scalar Attributes</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Intensity 🌸 (1–5)</label>
                <input name="intensity" type="number" min="1" max="5" value="{{ $v('intensity') }}"
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                       placeholder="1–5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valency</label>
                <select name="valency"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">— not set —</option>
                    <option value="0" {{ $v('valency') === '0' || $v('valency') === 0 ? 'selected' : '' }}>0 — Intransitive</option>
                    <option value="1" {{ $v('valency') == 1 ? 'selected' : '' }}>1 — Transitive</option>
                    <option value="2" {{ $v('valency') == 2 ? 'selected' : '' }}>2 — Ditransitive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Formula</label>
                <input name="formula" value="{{ $v('formula') }}"
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                       placeholder="[S] + 行 + [O]">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usage Note</label>
                <textarea name="usage_note" rows="3"
                          class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ $v('usage_note') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Learner Traps</label>
                <textarea name="learner_traps" rows="3"
                          class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ $v('learner_traps') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── Definitions (Alpine.js dynamic rows) ────────────────────── --}}
    @php
        $posOptions = [];
        foreach ($posLabels as $p) {
            if ($p->children->isNotEmpty()) {
                foreach ($p->children as $c) {
                    $posOptions[$c->id] = $c->slug . ' — ' . ($c->translations->first()?->label ?? $c->slug);
                }
            } else {
                $posOptions[$p->id] = $p->slug . ' — ' . ($p->translations->first()?->label ?? $p->slug);
            }
        }
        $langOptions = $languages->pluck('code', 'id')->toArray();
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 p-5"
         x-data="definitionRows({{ json_encode($initDefs) }})">

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900">Definitions</h3>
            <button type="button" @click="addRow()"
                    class="text-xs font-medium text-indigo-600 hover:text-indigo-800">+ Add definition</button>
        </div>

        <template x-for="(def, index) in defs" :key="index">
            <div class="grid grid-cols-12 gap-2 mb-3 items-start">
                {{-- Hidden ID for existing rows --}}
                <input type="hidden" :name="'definitions['+index+'][id]'" :value="def.id ?? ''">

                {{-- Language --}}
                <div class="col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Language</label>
                    <select :name="'definitions['+index+'][language_id]'" x-model="def.language_id"
                            class="block w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">—</option>
                        @foreach ($langOptions as $lid => $code)
                            <option value="{{ $lid }}">{{ $code }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- POS --}}
                <div class="col-span-3">
                    <label class="block text-xs text-gray-500 mb-1">POS</label>
                    <select :name="'definitions['+index+'][pos_id]'" x-model="def.pos_id"
                            class="block w-full rounded-lg border border-gray-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">— select —</option>
                        @foreach ($posOptions as $pid => $label)
                            <option value="{{ $pid }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Definition text --}}
                <div class="col-span-6">
                    <label class="block text-xs text-gray-500 mb-1">Definition</label>
                    <input :name="'definitions['+index+'][definition_text]'" x-model="def.definition_text"
                           type="text" placeholder="Definition text…"
                           class="block w-full rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <input type="hidden" :name="'definitions['+index+'][sort_order]'" :value="index">
                </div>

                {{-- Remove --}}
                <div class="col-span-1 flex items-end pb-1.5">
                    <button type="button" @click="removeRow(index)"
                            class="text-red-400 hover:text-red-600 text-xs" x-show="defs.length > 1">✕</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Submit --}}
    <div class="flex gap-3">
        <button type="submit"
                class="px-5 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
            {{ $sense ? 'Save changes' : 'Create sense' }}
        </button>
        <a href="{{ route('admin.words.show', $word) }}"
           class="px-5 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
            Cancel
        </a>
    </div>

</form>

<script>
function definitionRows(initial) {
    return {
        defs: initial.length ? initial : [{ id: null, language_id: '', pos_id: '', definition_text: '', sort_order: 0 }],
        addRow() {
            this.defs.push({ id: null, language_id: '', pos_id: '', definition_text: '', sort_order: this.defs.length });
        },
        removeRow(index) {
            if (this.defs.length > 1) this.defs.splice(index, 1);
        },
    };
}
</script>
