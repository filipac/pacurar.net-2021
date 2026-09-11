<x-layouts.master>
    <x-slot name="belowContent">
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
            {{-- Archive header --}}
            <div class="journal-heading mb-12">
                <p class="eyebrow mb-4">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Din viață. Din când în când.' : 'Notes from life, now and then.' }}</p>
                <h1 class="font-headline text-4xl md:text-6xl font-bold text-on-surface">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Gazeta personala' : 'Personal gazette' }}</h1>
                <div class="font-label text-xs uppercase tracking-wider mt-2" style="color: var(--color-on-surface-variant);">
                    @php
                        global $wp_query;
                        $total = $wp_query->found_posts ?? 0;
                    @endphp
                    {{ $total }} {{ ICL_LANGUAGE_CODE == 'ro' ? ($total == 1 ? 'articol' : 'articole') : ($total == 1 ? 'entry' : 'entries') }}
                </div>
            </div>

            <div class="journal-browse flex flex-wrap items-center justify-between gap-4 mb-6">
                <span class="font-label text-xs uppercase tracking-wider">{{ ICL_LANGUAGE_CODE == 'ro' ? 'De răsfoit, pe îndelete' : 'Take your time, have a browse' }}</span>
                <a href="{{ home_url('/random') }}" class="journal-random inline-flex items-center gap-2 text-sm font-semibold">
                    {{ ICL_LANGUAGE_CODE == 'ro' ? 'Surprinde-mă' : 'Surprise me' }}
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
            @include('partials.posts')
            @include('partials.pagination')
        </div>
    </x-slot>
</x-layouts.master>
