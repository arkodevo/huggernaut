<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Idioms — 流動 Living Lexicon</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+TC:wght@300;400;600;700&family=DM+Mono:ital,wght@0,300;0,400;1,300&family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
@include('partials.lexicon._foundations')
<style>
.cs-main { max-width: 640px; margin: 0 auto; padding: 2rem 1rem 3rem; text-align: center; }
.cs-title { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: var(--ink); margin-bottom: 0.5rem; }
.cs-desc { font-family: 'Cormorant Garamond', serif; font-size: 1rem; color: var(--dim); line-height: 1.6; max-width: 400px; margin: 0 auto; }
.cs-coming { font-family: 'DM Mono', monospace; font-size: 0.7rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--accent); margin-top: 2rem; opacity: 0.6; }
</style>
</head>
<body>
@include('partials.lexicon._site-header')

<div class="cs-main">
  <div class="cs-coming">Coming Soon</div>
  <h1 class="cs-title">Idioms</h1>
  <p class="cs-desc">Explore Chinese idioms and proverbs. Understand their origins, meanings, and how to use them naturally in conversation.</p>
</div>

@include('partials.lexicon._site-footer')
</body>
</html>
