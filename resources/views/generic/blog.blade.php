@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
    <x-content-with-sidebar>
        <div class="mb-4">
            <x-web3-ad spaceName="blog-top" />
        </div>
        @include('partials.posts')
        <div class="mt-4">
            <x-web3-ad spaceName="blog-after" />
        </div>
        @include('partials.pagination')
    </x-content-with-sidebar>
    @include('partials.copy')
@overwrite
