@props([
    'extraClassesContent',
    'containerStyles',
    'belowContent',
    'usesLivewire' => false,
])
<!DOCTYPE html>
<html {!! get_language_attributes() !!} prefix="og: http://ogp.me/ns#"
      x-data="{ darkMode: localStorage.getItem('darkMode') !== null ? localStorage.getItem('darkMode') === 'true' : window.matchMedia('(prefers-color-scheme: dark)').matches, geekMode: localStorage.getItem('geekMode') === 'true' }"
      :class="{ 'dark': darkMode, 'geek-mode': geekMode }">
@include('partials.head')
<body {!! body_class('bg-surface text-on-surface font-body min-h-screen flex flex-col') !!}>

@if(env('APP_ENV')==='production')
    <script>
        // scripts only should be ran on production server.
    </script>
@endif

@stack('beforeContainer')

@include('partials.header')


{{ $belowContent ?? '' }}

@include('partials.footer')

</body>
</html>
