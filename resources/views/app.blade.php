<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#38bdf8">

    <title inertia>{{ config('app.name', 'MineTrax') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap">

    <!-- Scripts -->
    @routes
    @vite('resources/js/app.js')
    @inertiaHead

    @if(now()->month === 12 && now()->day >= 22 && now()->day <= 31)
    <link rel="stylesheet" type="text/css" href="/snow/css/snowfall.css">
    @endif

    <script>
        let defaultTheme = '{{ app(\App\Settings\ThemeSettings::class)->color_mode }}'
        window.colorMode = localStorage.theme || defaultTheme
        if (window.colorMode === 'dark') {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

    </script>

</head>
<body class="font-sans antialiased bg-cool-gray-200 dark:bg-cool-gray-900">
    {{--Show global loading indication till Vue take over--}}
    <div id="site-global-loader" class="flex h-screen justify-center items-center">
        @if (app(\App\Settings\ThemeSettings::class)->loading_gif)
        <img width="50" height="50" src="{{ app(\App\Settings\ThemeSettings::class)->loading_gif }}" alt="logo" class="w-14 h-14">
        @else
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
        @endif
    </div>

    @inertia
    <x-translations></x-translations>
    <x-php-vars-to-js-transformer></x-php-vars-to-js-transformer>

    @if(now()->month === 12 && now()->day >= 22 && now()->day <= 31)
    <snowfall>
        <snowflake><span>❄</span>️️</snowflake>
        <snowflake><span>❄</span>️️</snowflake>
        <snowflake><span>❄</span>️️</snowflake>
        <snowflake><span>❄</span>️️</snowflake>
        <snowflake><span>🍂</span>️️</snowflake>
        <snowflake><span>💝</span> ️️</snowflake>
        <snowflake><span>🌟</span>️</snowflake>
        <snowflake><span>🎅</span>️</snowflake>
        <snowflake><span>🎄</span>️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
        <snowflake><img src="/snow/img/snowflake.png">️</snowflake>
    </snowfall>
    @endif
</body>
</html>
