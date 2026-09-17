<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Conductor') : config('app.name', 'Conductor') }}
</title>

<meta name="description" content="{{ $description ?? 'Conductor surfaces AI-generated changelogs for Composer packages, so you know what changed before you update.' }}">
<link rel="canonical" href="{{ url()->current() }}">

<link rel="icon" href="/favicon.ico" sizes="any">

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
