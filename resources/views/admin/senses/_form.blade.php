@php
    $v = fn(string $field, $default = null) => old($field, $sense?->{$field} ?? $default);
    $selectedDesignations = $sense?->designations->pluck('id')->toArray() ?? [];
    $selectedDomains = $sense?->domains->sortBy('pivot.sort_order')->pluck('id')->toArray() ?? [];
    $singleSelectAttrs = ['channel', 'connotation', 'semantic-mode', 'sensitivity', 'tocfl-level', 'hsk-level'];

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

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alignment</label>
                <select name="alignment"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">— Unset</option>
                    @foreach (['full' => '🤓 Full', 'partial' => '🤨 Partial', 'disputed' => '😵‍💫 Disputed'] as $val => $label)
                        <option value="{{ $val }}" {{ $v('alignment') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                <select name="source"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">— Unset</option>
                    @foreach (['tocfl' => 'TOCFL', 'editorial' => 'Editorial'] as $val => $label)
                        <option value="{{ $val }}" {{ $v('source') === $val ? 'selected' : '' }}>{{ $label }}</option>
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

    {{-- ── Domains (4 positions, ordered by relevance) ──────────────── --}}
    @php
        $domainAttr = $attributes['domain'] ?? null;
        $oldDomains = old('domains', $selectedDomains);
    @endphp
    @if ($domainAttr)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Domains <span class="text-xs font-normal text-gray-500">(most relevant first)</span></h3>
        <div class="grid grid-cols-2 gap-3">
            @for ($di = 0; $di < 4; $di++)
                @php $currentId = $oldDomains[$di] ?? ''; @endphp
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Position {{ $di + 1 }}{{ $di === 0 ? ' — most relevant' : '' }}</label>
                    <select name="domains[]"
                            class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">— none —</option>
                        @foreach ($domainAttr->designations as $des)
                            <option value="{{ $des->id }}" {{ $currentId == $des->id ? 'selected' : '' }}>
                                {{ $des->labels->first()?->label ?? $des->slug }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endfor
        </div>
    </div>
    @endif

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
        <div class="grid grid-cols-2 gap-4">
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
        </div>
    </div>

    {{-- ── Bilingual Notes (dynamic note types) ────────────────────── --}}
    @php
        $coverageLangs = \App\Models\Language::where('has_notes_coverage', true)->orderBy('id')->get();
        $noteTypes = \DB::table('note_types')->orderBy('sort_order')->get();

        // Build existing notes lookup: note_slug => lang_id => content
        $existingNotes = collect();
        if ($sense) {
            $existingNotes = \DB::table('word_sense_notes')
                ->join('note_types', 'word_sense_notes.note_type_id', '=', 'note_types.id')
                ->where('word_sense_notes.word_sense_id', $sense->id)
                ->select('note_types.slug', 'word_sense_notes.language_id', 'word_sense_notes.content')
                ->get()
                ->groupBy('slug')
                ->map(fn ($rows) => $rows->pluck('content', 'language_id'));
        }

        // Per-slug styling for the collapsible sections
        $noteTypeStyles = [
            'formula'       => ['border' => 'border-gray-200', 'bg' => 'bg-gray-50',  'headerBorder' => 'border-gray-200', 'title' => 'text-gray-900', 'badge' => 'text-gray-400', 'input' => 'input'],
            'usage_note'    => ['border' => 'border-amber-200', 'bg' => 'bg-amber-50', 'headerBorder' => 'border-amber-200', 'title' => 'text-amber-800', 'badge' => 'text-amber-500', 'input' => 'textarea'],
            'learner_traps' => ['border' => 'border-red-200',   'bg' => 'bg-red-50',   'headerBorder' => 'border-red-200',   'title' => 'text-red-800',   'badge' => 'text-red-400',   'input' => 'textarea'],
        ];
        $defaultStyle = ['border' => 'border-gray-200', 'bg' => 'bg-gray-50', 'headerBorder' => 'border-gray-200', 'title' => 'text-gray-900', 'badge' => 'text-gray-400', 'input' => 'textarea'];
    @endphp

    @foreach ($noteTypes as $nt)
        @php
            $ntLabel = \DB::table('note_type_labels')->where('note_type_id', $nt->id)->where('language_id', 1)->value('label')
                       ?? str_replace('_', ' ', ucfirst($nt->slug));
            $style = $noteTypeStyles[$nt->slug] ?? $defaultStyle;
            $isInput = ($style['input'] === 'input');
        @endphp
        <div class="bg-white rounded-xl {{ $style['border'] }} overflow-hidden">
            <details open>
                <summary class="px-5 py-3 {{ $style['bg'] }} border-b {{ $style['headerBorder'] }} cursor-pointer">
                    <span class="text-sm font-semibold {{ $style['title'] }}">{{ $ntLabel }}</span>
                    <span class="text-xs {{ $style['badge'] }} ml-2">{{ $coverageLangs->pluck('code')->implode(' + ') }}</span>
                </summary>
                <div class="p-5 space-y-3">
                    @foreach ($coverageLangs as $cl)
                        @php
                            $oldKey = "notes.{$cl->id}.{$nt->slug}";
                            $saved = $existingNotes->get($nt->slug)?->get($cl->id) ?? '';
                        @endphp
                        <div>
                            <label class="block text-xs font-semibold text-indigo-500 mb-1">{{ strtoupper($cl->code) }} · {{ $cl->name }}</label>
                            @if ($isInput)
                                <input name="notes[{{ $cl->id }}][{{ $nt->slug }}]"
                                       value="{{ old($oldKey, $saved) }}"
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                       placeholder="[S] + ... + [O]">
                            @else
                                <textarea name="notes[{{ $cl->id }}][{{ $nt->slug }}]" rows="2"
                                          class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old($oldKey, $saved) }}</textarea>
                            @endif
                        </div>
                    @endforeach
                </div>
            </details>
        </div>
    @endforeach

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
