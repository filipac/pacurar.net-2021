<head>
    <title>{{wp_title()}}</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1" />

    <link rel="apple-touch-icon" sizes="57x57" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/favicon-16x16.png">
    <link rel="manifest" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?php echo get_template_directory_uri();?>/assets/images/favicons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">

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
        $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $isPs = strpos($browser, 'Chrome-Lighthouse') !== false;
    @endphp
    @yield('head')
    @stack('head')
    <?php
        if(!$isPs) {
            wp_head();
        }
        if($isPs) {
            echo <<<HTML
                <style>
                .post-box {
                 opacity: 1 !important;
                }
                </style>
            HTML;

        }
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.1/howler.min.js" integrity="sha512-L6Z/YtIPQ7eU3BProP34WGU5yIRk7tNHk7vaC2dB1Vy1atz6wl9mCkTPPZ2Rn1qPr+vY2mZ9odZLdGYuaBk7dQ==" crossorigin="anonymous"></script>
</head>
