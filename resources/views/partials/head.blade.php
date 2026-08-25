<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'SMIS') : config('app.name', 'SMIS') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon-32x32.png" type="image/png" sizes="32x32">
<link rel="icon" href="/favicon-192x192.png" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=noto-sans-sinhala:400,500,600,700|noto-sans-tamil:400,500,600,700" rel="stylesheet" />

@fonts
@stack('styles')

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
