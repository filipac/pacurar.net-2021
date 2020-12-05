@extends('layouts.master')

@section('extraClassesContent') min-h-header-home @endsection

@section('below-content')
    <div class="">
        @include('partials.posts')
        @include('partials.pagination')
    </div>
@overwrite
