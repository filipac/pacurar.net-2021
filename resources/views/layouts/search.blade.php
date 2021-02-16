<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
@include('partials.head')
<body {!! body_class('bg-white dark:bg-black min-h-screen flex flex-col') !!}>

@if(env('APP_ENV')==='production')
    <script>
        // scripts only should be ran on production server.
    </script>
@endif

@stack('beforeContainer')

@php
$cls = Cookie::get('containerfull', 0) == 1 ? 'containerfull' : 'container';
@endphp

<div class="{{ $cls }} maincontainer @yield('containerStyles')" x-data="{gem: false}" style="z-index: 1;"
@creak.window="new Howl({
  src: ['{{ public_url('fun/wood.mp3') }}'],
  autoplay: true,
})">
    <div class="my-8 w-full border-2 shadow-box shadow-button border-black p-4 bg-white flex-1 flex flex-col @yield('extraClassesContent')"
         style="">
        @include('partials.header')

        @yield('content')
    </div>
    @yield('below-content')

    <audio x-ref="creak">
        <source src="{{ public_url('fun/wood.mp3') }}" type="audio/mpeg">
    </audio>
</div>


@include('partials.footer')

</body>
</html>
