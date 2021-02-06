@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
@inject('helper', 'Yoast\WP\SEO\Helpers\Current_Page_Helper')
    {{-- @if($helper->is_author_archive())
    <div class="">
        Vezi tot ce a scris
    </div>
    @endif --}}
    <div class="">
        @include('partials.posts')
        @include('partials.pagination')
    </div>
@overwrite
