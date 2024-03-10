<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#38bdf8">

    @php
    // SEO Favicon
    $faviconPath = app(\App\Settings\SeoSettings::class)->favicon_path;
    if ($faviconPath) {
        echo '<link rel="icon" href="' . $faviconPath . '">';
    }

    // SEO Title
    $titleHome = app(\App\Settings\SeoSettings::class)->title_home;
    $titleSuffix = app(\App\Settings\SeoSettings::class)->title_suffix;
    $title = $titleHome ?? config('app.name', 'MineTrax');
    if ($titleSuffix) {
        $title .= ' ' . $titleSuffix;
    }
    echo '<title inertia>' . $title . '</title>';
    @endphp

    {{-- SEO Meta Tags --}}
    @foreach (app(\App\Settings\SeoSettings::class)->meta as $metaTag)
    <meta name="{{ $metaTag['name'] }}" content="{{ $metaTag['content'] }}">
    @endforeach

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap">

    <!-- Scripts -->
    @routes
    @vite('resources/'.config('app.theme', 'default').'/js/app.js', 'build/' . config('app.theme', 'default'))
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

    {{-- SEO Inject At Head --}}
    @php
    $seoInjectAtHead = app(\App\Settings\SeoSettings::class)->inject_at_head;
    if ($seoInjectAtHead) {
        echo $seoInjectAtHead;
    }
    @endphp
</head>
<body class="font-sans antialiased bg-cool-gray-200 dark:bg-cool-gray-900">
    {{-- SEO Inject at Body Start --}}
    @php
    $seoInjectAtBodyStart = app(\App\Settings\SeoSettings::class)->inject_at_body_start;
    if ($seoInjectAtBodyStart) {
        echo $seoInjectAtBodyStart;
    }
    @endphp
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

    {{-- SEO Inject At Body End --}}
    @php
    $seoInjectAtBodyEnd = app(\App\Settings\SeoSettings::class)->inject_at_body_end;
    if ($seoInjectAtBodyEnd) {
        echo $seoInjectAtBodyEnd;
    }
    @endphp
</body>
</html>
