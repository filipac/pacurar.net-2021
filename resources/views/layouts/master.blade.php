<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
@include('partials.head')
<body {!! body_class('bg-splash min-h-screen flex flex-col') !!}>

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
@creak.window="$refs.creak.play()">
    <div class="my-8 mx-4 md:mx-0 w-full border-2 shadow-box shadow-button border-black p-4 bg-yellow flex-1 flex flex-col @yield('extraClassesContent')"
         style="background-image: url('/wp-content/uploads/2019/11/panorama_nov-2.jpg');
            background-size: 100% auto;
            background-repeat: no-repeat;
            background-blend-mode: color-burn;
            background-clip: border-box;
            background-position: 54% 0%;">
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
