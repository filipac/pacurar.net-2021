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
    <h1 class="text-white text-center text-3xl">My work and portfolio</h1>
    <div class="w-full bg-white rounded p-4 shadow-box mt-4 prose md:prose-xl max-w-none">
        <p class="font-bold">Hey there, just a quick heads-up!</p>
        <p>This page contains only a subset of my work, projects that I am proud of and also the ones that I can share.</p>
        <p>Most of my projects are under some terms though and I cannot publicly share them because they belong 100% to the client. I can however give you an idea of what I've built those past 3 years:</p>
        <ul>
            <li>Two intranet portals for an international business company</li>
            <li>National web app for online prescriptions and medication delivery</li>
            <li>International platform to connect to experts in various categories like motivation, sleep, lifestyle, nutrition or pregnancy</li>
            <li>Website for a national olympic team</li>
            <li>Advanced app for marketing leads collection</li>
            <li>Online courses marketplace</li>
            <li>Web app to see information about movies</li>
            <li>Dozens or tens of medium sized websites. Actually, I've looked at all repositories I still have access to and <strong>since 2018 I've built 55 websites</strong>. That's 1 and a half per month. Sure, not all of them were only my job, I've had help, but I touched at least 55 websites since 2018. That's something, isn't it?</li>
            <li>Private social media for a niche category</li>
            <li>Backend for an international singer</li>
        </ul>
        <p>Those are just a few examples of things that kept me busy since 2018, all done as contractor.</p>
        <p>If you want to see more of my work in private, send me an email to <strong>filip@pacurar.dev</strong> and we'll chat more about your digital needs too.</p>
        <p><strong>Enough chit-chat!</strong> Let me show you a subset of my portofolio now. You can also filter those projects by the technologies used.</p>
    </div>
    <div class="text-white mb-1 mt-12">Filter by the technologies used:</div>
    <livewire:portfolio-filters />
    <livewire:portfolio-items />
</div>

<div class="mt-12">
    @php
        global $showOnly;
        $showOnly = true;
    @endphp
    {!! comments_template('/comments.php') !!}
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
