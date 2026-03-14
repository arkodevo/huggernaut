@php
    $val = fn(string $field, $default = '') => old($field, $badge?->$field ?? $default);
@endphp

@if ($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm space-y-1">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

{{-- Basic identity --}}
<fieldset class="space-y-4">
    <legend class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Identity</legend>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-gray-400 text-xs">(stable, never change)</span></label>
            <input type="text" name="slug" value="{{ $val('slug') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="word-collector">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
            <input type="text" name="name" value="{{ $val('name') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="Word Collector">
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
            <input type="text" name="icon" value="{{ $val('icon', '🏅') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="🏅">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
            <input type="number" name="sort_order" value="{{ $val('sort_order', 0) }}" min="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="flex items-end pb-2">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       {{ $val('is_active', true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Active
            </label>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="2"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                  placeholder="Saved your first word. Every great lexicon starts somewhere.">{{ $val('description') }}</textarea>
    </div>
</fieldset>

{{-- Trigger --}}
<fieldset class="space-y-4 pt-4 border-t border-gray-200" x-data="{ trigger: '{{ $val('trigger_type', 'points_total') }}' }">
    <legend class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Trigger</legend>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Trigger Type</label>
        <select name="trigger_type" x-model="trigger"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="points_total" {{ $val('trigger_type') === 'points_total' ? 'selected' : '' }}>Points total (lifetime earned ≥ threshold)</option>
            <option value="action_count" {{ $val('trigger_type') === 'action_count' ? 'selected' : '' }}>Action count (event_type count ≥ threshold)</option>
            <option value="streak"       {{ $val('trigger_type') === 'streak'       ? 'selected' : '' }}>Streak (login streak ≥ threshold days)</option>
            <option value="manual"       {{ $val('trigger_type') === 'manual'       ? 'selected' : '' }}>Manual (admin-granted only)</option>
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4" x-show="trigger !== 'manual'">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Threshold</label>
            <input type="number" name="threshold" value="{{ $val('threshold', 0) }}" min="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div x-show="trigger === 'action_count'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Action Type <span class="text-gray-400 text-xs">(event_type slug)</span></label>
            <input type="text" name="action_type" value="{{ $val('action_type') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                   placeholder="word_saved">
        </div>
    </div>
    {{-- Hidden threshold for manual badges (required by validation) --}}
    <input type="hidden" name="threshold" value="0" x-show="trigger === 'manual'">
</fieldset>

{{-- Reward --}}
<fieldset class="space-y-4 pt-4 border-t border-gray-200">
    <legend class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Reward</legend>

    <div class="max-w-xs">
        <label class="block text-sm font-medium text-gray-700 mb-1">Bonus AI Credits</label>
        <input type="number" name="bonus_credits" value="{{ $val('bonus_credits', 0) }}" min="0" max="65535"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p class="text-xs text-gray-400 mt-1">Awarded once when the badge is first earned.</p>
    </div>
</fieldset>
