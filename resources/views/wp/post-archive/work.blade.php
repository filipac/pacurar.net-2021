@php
        /**
         * Filters the text of the page title.
         *
         */
        add_filter('wp_title', fn( string $title ) => 'My work and portfolio - Filip Iulian Pacurar', 20, 1 )
@endphp
@extends('layouts.master')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
@endpush
@push('scripts')
    @livewireScripts
@endpush

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
<div class="px-4 md:px-0">
    <livewire:portfolio-filters />
    <livewire:portfolio-items />
</div>

@include('partials.copy')

<script>
document.addEventListener('livewire:load', function() {
    Livewire.hook('element.updated', function(el, component) {
        setTimeout(function() {
            if (component.fingerprint.name == 'portfolio-items') {
                window.showAll()
            }
        })
    })
})
</script>
@overwrite
