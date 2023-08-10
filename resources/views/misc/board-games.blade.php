@php
    add_action('wpseo_opengraph_title', fn() => 'My board games - Filip Pacurar');
@endphp

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('scriptsEnd')
    <script>

        document.addEventListener('livewire:initialized', () => {
            // alert('da')
        })

        document.addEventListener('livewire:available', () => {
            Livewire.on('gamesLoaded', function () {
                rerenderLivewireableComponents()
            });
        })
    </script>
@endpush

<x-layouts.master>
    <x-slot name="title">My board games - Filip Pacurar</x-slot>
    <x-slot name="belowContent">
        <x-content-with-sidebar>
            <livewire:board-games/>
        </x-content-with-sidebar>

        @include('partials.copy')
    </x-slot>
</x-layouts.master>
