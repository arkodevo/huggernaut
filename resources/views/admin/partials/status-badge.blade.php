@php
    $colors = [
        'published' => 'bg-green-100 text-green-800',
        'review'    => 'bg-amber-100 text-amber-800',
        'draft'     => 'bg-gray-100 text-gray-600',
    ];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colors[$status] ?? 'bg-gray-100 text-gray-600' }}">
    {{ ucfirst($status) }}
</span>
