@extends('layouts.master')

@push('beforeContainer')
    <div class="overflow-hidden">
        {{-- <iframe width="560" height="315" class="w-full h-screen fixed skip"
        style="z-index: -1;object-fit: cover;"
        src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?controls=0&autoplay=1&disablekb=1&fs=0&loop=1&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> --}}
        <video width="100%" id="rick" class="w-full h-screen fixed skip" autoplay muted style="z-index: -1;object-fit: cover;">
            <!-- HTML 5 browsers will play one of these -->
            <source src="{{ public_url('fun/rick.webm') }}" type="video/webm" /></source>
            <source src="{{ public_url('fun/rick.mp4') }}" type="video/mp4" /></source>
            You need an HTML 5-capable browser.
        </video>
    </div>
@endpush

@section('containerStyles') opacity: 0.4 @endsection

@section('below-content')
    <div class="m-8">
    <div class="flex items-center justify-center">
        <section class="flex flex-col text-center bg-splash text-white w-auto p-12 shadow-box hover:shadow-boxhvr perspective-sm">
            <img src="{{ public_url('fun/bad_vibes.gif') }}" alt="">
            <div>
                @if(ICL_LANGUAGE_CODE == 'ro')
                <h1 class="text-4xl">404</h1>
                <p>Pagina pe care o cauti nu exista.<br/>
                Dar m-am gandit ca iti va placea acest video mirobolant.<br />
                Click oriunde in pagina sa porneasca si audio.<br/> <br/>
                (browserele sunt rele si nu ne lasa sa dam play la un video cu sunet)</p>
                @else
                <h1 class="text-4xl">404</h1>
                <p>The page you are looking for does not exist.<br/>
                But... I thought you would like this awesome video.<br />
                Click anywhere on the page so the audio starts too.<br/> <br/>
                (browsers are evil and won't let us autoplay a video with sound)</p>
                @endif
            </div>
            <div class="mt-6">
                <a href="{{home_url()}}" class="inline-block bg-yellow perspective shadow-box hover:shadow-boxhvr text-black p-4">Inapoi la baza.</a>
            </div>
        </section>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        jQuery(document).on('click', function() {
            var element = document.querySelector('#rick')
            if(element) {
                element.muted = false;
                element.volume = 0.2;
            }
        })
    </script>
@endpush
