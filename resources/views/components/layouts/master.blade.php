@props([
    'extraClassesContent',
    'containerStyles',
    'belowContent',
])
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
$cls = Cookie::get('containerfull', 'nu') == 'da' ? 'containerfull' : 'container';
@endphp

<div class="{{ $cls }} maincontainer" x-data="{gem: false}" style="z-index: 1;{{$containerStyles ?? ''}}"
@creak.window="new Howl({
  src: ['{{ public_url('fun/wood.mp3') }}'],
  autoplay: true,
})">
    <div class="my-8 w-full border-2 shadow-box shadow-button border-black p-4 bg-primary flex-1 flex flex-col
        {{$extraClassesContent ?? ''}}"
         style="
            background-image: url('https://pacurar.net/wp-content/uploads/2022/04/drone-april-2022.jpg');
            background-size: 100% auto;
            background-repeat: no-repeat;
            background-blend-mode: normal;
            background-clip: border-box;
            background-position: 50% {{(is_single() || is_page()) ? '0' : '40'}}%;">
        @include('partials.header')

        {{ $slot }}
    </div>
    {{ $belowContent ?? '' }}

    <audio x-ref="creak">
        <source src="{{ public_url('fun/wood.mp3') }}" type="audio/mpeg">
    </audio>
</div>


@include('partials.footer')

</body>
</html>
