@php
    $title = 'My Board Game collection - Filip Iulian Pacurar';
    add_action('wpseo_opengraph_title', fn() => $title, 999);
    add_filter('wp_title', fn( string $title ) => $title, 20, 1 )
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

<x-layouts.master :title="$title">
    <x-slot name="belowContent">
        <x-content-with-sidebar>
            <livewire:board-games/>
        </x-content-with-sidebar>

        @include('partials.copy')
    </x-slot>
</x-layouts.master>
