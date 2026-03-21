<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — 流動 Living Lexicon</title>
    <style>
        :root { --gold: #a0720a; --accent: #6240c8; --ink: #1a1a1a; --dim: #888; --bg: #faf8f5; --border: #e8e4de; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Mono', 'SF Mono', monospace; background: var(--bg); color: var(--ink); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 380px; padding: 2rem 1.5rem; }
        .auth-logo { text-align: center; margin-bottom: 2rem; }
        .auth-logo-char { font-family: 'BiauKai', 'STKaiti', 'KaiTi', serif; font-size: 3rem; font-weight: 300; color: var(--ink); line-height: 1.1; }
        .auth-logo-sub { font-size: 0.6rem; color: var(--dim); letter-spacing: 0.25em; text-transform: uppercase; margin-top: 0.3rem; }
        .auth-form { display: flex; flex-direction: column; gap: 1rem; }
        .auth-label { display: block; font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--dim); margin-bottom: 0.3rem; }
        .auth-input { display: block; width: 100%; font-family: 'DM Mono', monospace; font-size: 0.85rem; padding: 0.6rem 0.75rem; border: 1px solid var(--border); border-radius: 3px; background: white; color: var(--ink); outline: none; transition: border-color 0.15s; }
        .auth-input:focus { border-color: var(--accent); }
        .auth-input.error { border-color: #c44; }
        .auth-error { font-size: 0.7rem; color: #c44; margin-top: 0.2rem; }
        .auth-btn { display: block; width: 100%; font-family: 'DM Mono', monospace; font-size: 0.8rem; letter-spacing: 0.08em; padding: 0.65rem; border: none; border-radius: 3px; background: var(--accent); color: white; cursor: pointer; transition: background 0.15s; margin-top: 0.5rem; }
        .auth-btn:hover { background: #5535b0; }
        .auth-links { text-align: center; margin-top: 1.5rem; font-size: 0.72rem; color: var(--dim); }
        .auth-links a { color: var(--accent); text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <div class="auth-logo-char">流動</div>
        <div class="auth-logo-sub">Living Lexicon</div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div>
            <label for="name" class="auth-label">Name</label>
            <input id="name" name="name" type="text" autocomplete="name" required
                   value="{{ old('name') }}"
                   class="auth-input @error('name') error @enderror">
            @error('name')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="email" class="auth-label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="auth-input @error('email') error @enderror">
            @error('email')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="password" class="auth-label">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required
                   class="auth-input @error('password') error @enderror">
            @error('password')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="auth-label">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                   class="auth-input">
        </div>

        <button type="submit" class="auth-btn">Create Account</button>
    </form>

    <div class="auth-links">
        Already have an account? <a href="{{ route('login') }}">Log in</a>
    </div>
</div>
</body>
</html>
