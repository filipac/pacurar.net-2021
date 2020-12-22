@yield('footer')
@php
    wp_footer()
@endphp
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-53615822-4"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-53615822-4');
</script>
@if(Str::startsWith(mix('js/app.js'), '/'))
<script src="{{ public_url(mix('js/app.js')) }}"></script>
@else
<script src="{{ mix('js/app.js') }}"></script>
@endif
@stack('scripts')
