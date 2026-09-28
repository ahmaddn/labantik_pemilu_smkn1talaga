<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

@php
    $customFavicon = \App\Models\AppSettingEvote::getValue('app_favicon', '/favicon.ico');
    $appName = \App\Models\AppSettingEvote::getValue('app_name', 'LabAntik Pemilu SMKN 1 Talaga');

    $faviconUrl = $customFavicon;
    if ($customFavicon && $customFavicon !== '/favicon.ico' && file_exists(public_path($customFavicon))) {
        $faviconUrl .= '?v=' . filemtime(public_path($customFavicon));
    } else {
        $faviconUrl .= '?v=' . time();
    }
@endphp
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <script>
            (function() {
                const saved = localStorage.getItem('theme');
                if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ $appName }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
        <x-inertia::app />
    </body>
</html>
