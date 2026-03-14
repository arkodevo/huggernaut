<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — 流動 Admin</title>
    @vite(['resources/css/app.css'])
</head>
<body class="h-full flex items-center justify-center">

<div class="w-full max-w-sm px-6">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="text-5xl font-bold text-white mb-1">流動</div>
        <div class="text-xs text-gray-500 uppercase tracking-widest">Living Lexicon · Admin</div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-400 mb-1">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="block w-full rounded-lg bg-gray-900 border border-gray-700 text-white
                          px-3 py-2.5 text-sm placeholder-gray-600
                          focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500
                          @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-400 mb-1">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                   class="block w-full rounded-lg bg-gray-900 border border-gray-700 text-white
                          px-3 py-2.5 text-sm
                          focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </div>

        {{-- Remember --}}
        <div class="flex items-center gap-2">
            <input id="remember" name="remember" type="checkbox"
                   class="h-4 w-4 rounded border-gray-700 bg-gray-900 text-indigo-600">
            <label for="remember" class="text-sm text-gray-400">Remember me</label>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white
                       hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       focus:ring-offset-gray-950 transition-colors">
            Sign in
        </button>
    </form>

</div>

</body>
</html>
