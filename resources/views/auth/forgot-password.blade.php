<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — 流動 Living Lexicon</title>
    <style>
        :root { --gold: #a0720a; --accent: #6240c8; --ink: #1a1a1a; --dim: #888; --bg: #faf8f5; --border: #e8e4de; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Mono', 'SF Mono', monospace; background: var(--bg); color: var(--ink); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 380px; padding: 2rem 1.5rem; }
        .auth-logo { text-align: center; margin-bottom: 2rem; }
        .auth-logo-char { font-family: 'BiauKai', 'STKaiti', 'KaiTi', serif; font-size: 3rem; font-weight: 300; color: var(--ink); line-height: 1.1; }
        .auth-logo-sub { font-size: 0.6rem; color: var(--dim); letter-spacing: 0.25em; text-transform: uppercase; margin-top: 0.3rem; }
        .auth-hint { font-size: 0.75rem; color: var(--dim); line-height: 1.5; margin-bottom: 1.5rem; text-align: center; }
        .auth-form { display: flex; flex-direction: column; gap: 1rem; }
        .auth-label { display: block; font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--dim); margin-bottom: 0.3rem; }
        .auth-input { display: block; width: 100%; font-family: 'DM Mono', monospace; font-size: 0.85rem; padding: 0.6rem 0.75rem; border: 1px solid var(--border); border-radius: 3px; background: white; color: var(--ink); outline: none; transition: border-color 0.15s; }
        .auth-input:focus { border-color: var(--accent); }
        .auth-input.error { border-color: #c44; }
        .auth-error { font-size: 0.7rem; color: #c44; margin-top: 0.2rem; }
        .auth-status { font-size: 0.75rem; color: #2a7d2a; background: #eef7ee; padding: 0.6rem 0.75rem; border-radius: 3px; margin-bottom: 0.5rem; }
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

    <div class="auth-hint">
        Enter your email and we'll send you a link to reset your password.
    </div>

    @if (session('status'))
        <div class="auth-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div>
            <label for="email" class="auth-label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required
                   value="{{ old('email') }}"
                   class="auth-input @error('email') error @enderror">
            @error('email')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="auth-btn">Send Reset Link</button>
    </form>

    <div class="auth-links">
        <a href="{{ route('login') }}">Back to log in</a>
    </div>
</div>
</body>
</html>
