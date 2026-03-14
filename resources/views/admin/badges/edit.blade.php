@extends('admin.layout')

@section('title', 'Edit Badge')

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.badges.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Badges</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Badge</h1>
</div>

<form method="POST" action="{{ route('admin.badges.update', $badge) }}" class="max-w-2xl space-y-6">
    @csrf @method('PUT')
    @include('admin.badges._form', ['badge' => $badge])
    <div class="flex gap-3">
        <button type="submit"
                class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            Save Changes
        </button>
        <a href="{{ route('admin.badges.index') }}"
           class="px-5 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
            Cancel
        </a>
    </div>
</form>

@endsection
