<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1"/>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">

<link rel="apple-touch-icon" sizes="57x57"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16"
      href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/favicon-16x16.png">
<link rel="manifest" href="{{get_stylesheet_directory_uri().'/resources'}}/favicons/manifest.json">
<meta name="msapplication-TileColor" content="#003849">
<meta name="msapplication-TileImage"
      content="<?php echo get_template_directory_uri();?>/assets/images/favicons/ms-icon-144x144.png">
<meta name="theme-color" content="#003849">

<link rel="shortcut icon" href="{{ public_url('images/favicon.ico') }}" type="image/vnd.microsoft.icon"/>
<link rel="apple-touch-icon-precomposed" href="{{ public_url('images/apple-touch-icon-precomposed.png') }}">

<link rel="preload" as="font"
      href="/wp-content/themes/pacurar2020/resources/fonts/bariol_regular-webfont.woff"
      type="font/woff" crossorigin="anonymous">

{{-- Text fonts loaded non-blocking to avoid render-blocking (display=swap handles FOUT) --}}
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Inter:wght@300..700&family=JetBrains+Mono:ital,wght@0,300..700;1,300..700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Inter:wght@300..700&family=JetBrains+Mono:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet"></noscript>
{{-- Keep the icon font non-blocking and limited to the glyphs used by the theme. --}}
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&amp;icon_names=arrow_forward,article,close,code,dark_mode,light_mode,mail,menu,open_in_new,photo_camera,play_circle,search,share,terminal&amp;display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,400,0,0&amp;icon_names=arrow_forward,article,close,code,dark_mode,light_mode,mail,menu,open_in_new,photo_camera,play_circle,search,share,terminal&amp;display=swap" rel="stylesheet"></noscript>

<!-- Styles blog -->
@vite(['resources/sass/tailwind.css', 'resources/sass/app.scss'])
@php
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $isPs = strpos($browser, 'Chrome-Lighthouse') !== false;
@endphp
{{ $head ?? '' }}
@yield('head')
@stack('head')
<?php
if (!$isPs) {
    wp_head();
}
?>

@if($usesLivewire ?? false)
    @livewireStyles
@endif
