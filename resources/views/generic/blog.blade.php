@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
    <x-content-with-sidebar>
{{--        <div data-replace-vue-app></div>--}}
{{--        <div data-replace-vue-app></div>--}}
        @include('partials.posts')
        @include('partials.pagination')
    </x-content-with-sidebar>
{{--    @push('scripts')--}}
{{--        @viteReactRefresh--}}
{{--        @vite(['resources/js/react-app.tsx'])--}}
{{--    @endpush--}}
    @include('partials.copy')
@overwrite
