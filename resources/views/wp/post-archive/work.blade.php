@php
    /**
     * Filters the text of the page title.
     *
     */
    add_filter('wp_title', fn( string $title ) => 'My work and portfolio - Filip Iulian Pacurar', 20, 1 )
@endphp

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('scripts')
@endpush


<x-layouts.master>
    <x-slot name="belowContent">
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
            <div x-data='{text: true}'>
                <h1 class="font-headline text-4xl md:text-5xl font-bold text-on-surface mb-8">My work and portfolio</h1>
                <div class="p-6 md:p-8 prose dark:prose-invert md:prose-lg max-w-none" style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem;">
                    <p class="font-bold" x-show="text">Hey there, just a quick heads-up!</p>
                    <div class="w-full text-center">
                        <a href="#projects" x-show="text"
                           x-on:click.prevent="text = false; $nextTick(() => window.location='#projects')"
                           class="inline-flex items-center gap-2 px-4 py-2 font-label text-xs uppercase tracking-wider text-white transition-colors no-underline" style="background: var(--color-primary); border-radius: 0.125rem;">No time for talking, show me the projects!</a>
                        <template x-if="!text">
                            <div class="my-6">
                                <a href="#" x-on:click="text = true;"
                                   class="inline-flex items-center gap-2 px-4 py-2 font-label text-xs uppercase tracking-wider transition-colors no-underline" style="background: var(--color-surface-container); border-radius: 0.125rem;">Show me the intro text again!</a>
                            </div>
                        </template>
                    </div>
                    <div x-bind:class='{open: text}'>
                        <div x-show="text">
                            <p>This page contains only a subset of my work, projects that I am proud of and also the
                                ones that I can share.</p>
                            <p>Most of my projects are under some terms though and I cannot publicly share them
                                because they belong 100% to the client. I can however give you an idea of what I've
                                built those past 3 years:</p>
                            <ul>
                                <li>Two intranet portals for an international business company</li>
                                <li>National web app for online prescriptions and medication delivery</li>
                                <li>International platform to connect to experts in various categories like
                                    motivation, sleep, lifestyle, nutrition or pregnancy
                                </li>
                                <li>Website for a national olympic team</li>
                                <li>Advanced app for marketing leads collection</li>
                                <li>Online courses marketplace</li>
                                <li>Web app to see information about movies</li>
                                <li>Dozens or tens of medium sized websites. Actually, I've looked at all
                                    repositories I still have access to and <strong>since 2018 I've built 55
                                        websites</strong>. That's 1 and a half per month. Sure, not all of them were
                                    only my job, I've had help, but I touched at least 55 websites since 2018.
                                    That's something, isn't it?
                                </li>
                                <li>Private social media for a niche category</li>
                                <li>Backend for an international singer</li>
                            </ul>
                            <p>Those are just a few examples of things that kept me busy since 2018, all done as
                                contractor.</p>
                            <p>If you want to see more of my work in private, send me an email to <strong>filip@pacurar.dev</strong>
                                and we'll chat more about your digital needs too.</p>
                            <p><strong>Enough chit-chat!</strong> Let me show you a subset of my portofolio now. You
                                can also filter those projects by the technologies used.</p>
                        </div>
                    </div>
                </div>
                <div class="font-label text-xs uppercase tracking-wider mt-8 mb-3" style="color: var(--color-on-surface-variant);">Filter by technology:</div>
                <a href="#" id="projects"></a>
                <livewire:portfolio-filters/>
                <livewire:portfolio-items/>
            </div>

            <div class="mt-12">
                @php
                    global $showOnly;
                    $showOnly = true;
                @endphp
                {!! comments_template('/comments.php') !!}
            </div>
        </div>
    </x-slot>
</x-layouts.master>
