<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @routes
        @vite('resources/js/app.js')
        @inertiaHead

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
        @inertia
        <x-translations></x-translations>
    </body>
</html>
