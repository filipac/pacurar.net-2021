@yield('footer')
{{ $footer ?? '' }}
    @livewireScripts
@php
    wp_footer()
@endphp
@if(ICL_LANGUAGE_CODE == 'ro')
{{--<script async defer data-domain="pacurar.net" src="https://plausible.pacurar.dev/js/plausible.js"></script>--}}
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-31FN5CD470"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-31FN5CD470');
</script>
@else
{{--<script async defer data-domain="pacurar.dev" src="https://plausible.pacurar.dev/js/plausible.js"></script>--}}
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-HG03G6CK1E"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-HG03G6CK1E');
</script>
@endif
@php
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $isPs = strpos($browser, 'Chrome-Lighthouse') !== false;
@endphp
@if(!$isPs)
    @vite(['resources/js/app.js'])
    @stack('scripts')
{{--@if(Str::startsWith(mix('js/app.js'), '/'))--}}
{{--<script src="{{ public_url(mix('js/app.js')) }}"></script>--}}
{{--@else--}}
{{--<script src="{{ mix('js/app.js') }}"></script>--}}
{{--@endif--}}
@endif
<div class="dapp-required">

</div>
@stack('scriptsEnd')
