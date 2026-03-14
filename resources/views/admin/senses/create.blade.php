@extends('admin.layout')
@section('title', 'Add Sense — ' . $word->traditional)

@section('content')

<div class="mb-6">
    <a href="{{ route('admin.words.show', $word) }}" class="text-sm text-gray-500 hover:text-gray-700">← {{ $word->traditional }}</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-1">Add Sense</h1>
    <p class="text-sm text-gray-500">A sense = one pronunciation reading × one meaning cluster.</p>
</div>

@include('admin.senses._form', [
    'action'       => route('admin.words.senses.store', $word),
    'method'       => 'POST',
    'sense'        => null,
    'word'         => $word,
    'attributes'   => $attributes,
    'posLabels'    => $posLabels,
    'languages'    => $languages,
    'existingDefs' => [],
])

@endsection
