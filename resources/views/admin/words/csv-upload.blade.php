@extends('admin.layout')
@section('title', 'Import CSV')

@section('content')

<div class="max-w-xl">
    <a href="{{ route('admin.words.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Words</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2 mb-1">Import CSV</h1>
    <p class="text-sm text-gray-500 mb-6">Upload a CSV file with traditional Chinese characters (one per line). 師父 will generate senses, definitions, examples, and attributes for each word. Words are processed in batches of 10.</p>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.words.csv-import.process') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors">
                <p class="mt-2 text-xs text-gray-400">
                    One traditional character/word per line. No headers needed. More than 10 words will be queued and processed in batches.
                </p>
                @error('csv_file')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-5">
                <p class="text-xs text-amber-800">
                    <strong>How it works:</strong> 師父 will analyze each word and generate complete enrichment data (senses, POS, definitions in EN + ZH, examples, relations, attributes). You'll review everything before saving.
                </p>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 mb-5">
                <p class="text-xs font-medium text-gray-600 mb-1">Example CSV content:</p>
                <pre class="text-xs text-gray-500 font-mono">流
轉
準
明白
完成</pre>
            </div>

            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors">
                Upload & Process with 師父
            </button>
        </form>
    </div>
</div>

@endsection
