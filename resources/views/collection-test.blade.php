<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Test: {{ $collection['name'] }} — 流動 Living Lexicon</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
  @include('partials.lexicon._foundations')
  @include('partials.lexicon._attr-chip-css')
  @include('partials.lexicon._collection-test-css')
</head>
<body>
<script>
  window.__AUTH = @json($authUser);
  var CT_SENSES = @json($senses);
  var CT_DISTRACTORS = @json($distractors);
  var CT_COLLECTION = @json($collection);
</script>

@include('partials.lexicon._site-header', ['backUrl' => '/my-words', 'backLabel' => 'My Words'])

<div class="ct-page">
  <div id="ctApp"></div>
</div>

@include('partials.lexicon._collection-test-js')
<script>
  // Boot the test setup screen
  ctSetup();
</script>
@include('partials.lexicon._site-footer')
</body>
</html>
