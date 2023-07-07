@php
    add_action('wpseo_opengraph_title', fn() => 'My board games - Filip Pacurar');
@endphp
@section('title') My board games - Filip Pacurar @endsection

@extends('layouts.master')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
@endpush
@push('scriptsEnd')
    @livewireScripts
    <script>
        Livewire.on('gamesLoaded', function () {
            renderGameCounter()
        });
    </script>
@endpush

@section('below-content')
    <x-content-with-sidebar>
        <livewire:board-games />
    </x-content-with-sidebar>

    @include('partials.copy')
@endsection
