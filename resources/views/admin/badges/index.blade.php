@extends('admin.layout')

@section('title', 'Badges')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Badges</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $badges->count() }} badge{{ $badges->count() === 1 ? '' : 's' }} · earned automatically or granted manually</p>
    </div>
    <a href="{{ route('admin.badges.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
        + New Badge
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Badge</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Trigger</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Threshold</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Bonus Credits</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Order</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($badges as $badge)
                <tr class="hover:bg-gray-50 transition-colors {{ $badge->is_active ? '' : 'opacity-50' }}">

                    {{-- Icon + name + slug --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl leading-none">{{ $badge->icon }}</span>
                            <div>
                                <p class="font-medium text-gray-900">{{ $badge->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $badge->slug }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Trigger type --}}
                    <td class="px-5 py-3">
                        @php
                            $triggerLabels = [
                                'points_total' => ['label' => 'Points total', 'color' => 'bg-violet-100 text-violet-700'],
                                'action_count' => ['label' => 'Action count', 'color' => 'bg-blue-100 text-blue-700'],
                                'streak'       => ['label' => 'Streak',       'color' => 'bg-amber-100 text-amber-700'],
                                'manual'       => ['label' => 'Manual',       'color' => 'bg-gray-100 text-gray-600'],
                            ];
                            $t = $triggerLabels[$badge->trigger_type] ?? ['label' => $badge->trigger_type, 'color' => 'bg-gray-100 text-gray-600'];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $t['color'] }}">
                            {{ $t['label'] }}
                        </span>
                        @if ($badge->action_type)
                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $badge->action_type }}</p>
                        @endif
                    </td>

                    {{-- Threshold --}}
                    <td class="px-5 py-3 text-gray-700 font-mono">
                        {{ $badge->threshold > 0 ? number_format($badge->threshold) : '—' }}
                    </td>

                    {{-- Bonus credits --}}
                    <td class="px-5 py-3 text-gray-700">
                        {{ $badge->bonus_credits > 0 ? '+' . $badge->bonus_credits . ' ✨' : '—' }}
                    </td>

                    {{-- Status toggle --}}
                    <td class="px-5 py-3">
                        <form method="POST" action="{{ route('admin.badges.toggle', $badge) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="text-xs font-medium px-2.5 py-1 rounded-full transition-colors
                                           {{ $badge->is_active
                                               ? 'bg-green-100 text-green-700 hover:bg-red-100 hover:text-red-700'
                                               : 'bg-gray-100 text-gray-500 hover:bg-green-100 hover:text-green-700' }}">
                                {{ $badge->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>

                    {{-- Sort order --}}
                    <td class="px-5 py-3 text-gray-400 font-mono text-xs">
                        {{ $badge->sort_order }}
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.badges.edit', $badge) }}"
                           class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Edit
                        </a>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                        No badges yet. <a href="{{ route('admin.badges.create') }}" class="text-indigo-600 hover:underline">Create the first one.</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
