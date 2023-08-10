@php
global $wp_filter;
//$wp_filter['pre_get_document_title']->callbacks = [];
//$wp_filter['wp_title']->callbacks = [];
//$wp_filter['wpseo_head']->callbacks[30] = [];

@endphp
<x-layouts.master extra-classes-content=" min-h-header-home">
    <x-slot name="belowContent">
        @inject('helper', 'Yoast\WP\SEO\Helpers\Current_Page_Helper')
        {{-- @if($helper->is_author_archive())
        <div class="">
            Vezi tot ce a scris
        </div>
        @endif --}}
        <x-content-with-sidebar>
            <div class="">
                @include('partials.posts')
                @include('partials.pagination')
            </div>
        </x-content-with-sidebar>
        @include('partials.copy')
    </x-slot>
</x-layouts.master>
