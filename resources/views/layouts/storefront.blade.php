@php
$overridesCss = app(\App\Services\ThemeService::class)->getAllOverridesCss();
@endphp
<!doctype html>
<html lang="pt-BR" data-theme="verde">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Loja Teste' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($overridesCss)
        <style id="database-theme-tokens">{!! $overridesCss !!}</style>
    @endif
</head>
<body style="margin:0;background:#f5f5f5;">
    {{ $slot }}
</body>
</html>
