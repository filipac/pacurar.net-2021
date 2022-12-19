@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
    <x-content-with-sidebar>
        @include('partials.posts')
        @include('partials.pagination')
    </x-content-with-sidebar>
    @include('partials.copy')
@overwrite
