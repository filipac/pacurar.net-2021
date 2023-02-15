@section('title') My board games - Filip Pacurar @endsection

@extends('layouts.master')

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
@endpush
@push('scripts')
    @livewireScripts
@endpush

@section('below-content')
    <x-content-with-sidebar>
        <livewire:board-games />
    </x-content-with-sidebar>
@endsection
