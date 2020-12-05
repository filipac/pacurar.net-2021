@yield('footer')
@php
    wp_footer()
@endphp

@if(Str::startsWith(mix('js/app.js'), '/'))
<script src="{{ public_url(mix('js/app.js')) }}"></script>
@else
<script src="{{ mix('js/app.js') }}"></script>
@endif
@stack('scripts')
