@php
$overridesCss = app(\App\Services\ThemeService::class)->getAllOverridesCss();
$activeTheme = app(\App\Models\Theme::class)->where('is_active', true)->first();
$defaultSlug = $activeTheme?->slug ?? 'verde';
@endphp
<!doctype html>
<html lang="pt-BR" data-theme="{{ $defaultSlug }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($overridesCss)
        <style id="database-theme-tokens">{!! $overridesCss !!}</style>
    @endif
</head>
<body style="margin:0;min-height:100vh;display:flex;font-family:'Inter',system-ui,sans-serif;">
    {{ $slot }}
</body>
</html>
