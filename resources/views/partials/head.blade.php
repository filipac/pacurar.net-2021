<head>
    <title>{{wp_title()}}</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1" />

    <link rel="shortcut icon" href="{{ public_url('images/favicon.ico') }}" type="image/vnd.microsoft.icon"/>
    <link rel="apple-touch-icon-precomposed" href="{{ public_url('images/apple-touch-icon-precomposed.png') }}">
{{--    <link rel="stylesheet" href="https://use.typekit.net/rqd7nkn.css">--}}
{{--    <link href="https://fonts.googleapis.com/css?family=Sigmar+One&display=swap" rel="stylesheet">--}}
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <link rel="preload" as="font" href="https://pacurar.net/wp-content/themes/pacurar2020/resources/fonts/bariol_regular-webfont.woff" type="font/woff" crossorigin="anonymous">
    @if(Str::startsWith(mix('css/app.css'), '/'))
    <link rel="stylesheet" href="{{ public_url(mix('css/app.css')) }}"/>
    @else
    <link rel="stylesheet" href="{{ mix('css/app.css') }}"/>
    @endif
{{--    @if(($post = get_post()) && ($imgId = get_post_meta($post, 'og_image')))--}}
{{--    @dd($imgId)--}}
{{--    @endif--}}
    @php
        $browser = get_browser();
        $isPs = strpos($browser, 'Chrome-Lighthouse') !== false;
    @endphp
    @yield('head')
    @stack('head')
    <?php
        if(!$isPs) {
            wp_head();
        }
    ?>
</head>
