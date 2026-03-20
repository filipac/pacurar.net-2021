<x-layouts.master>
    <x-slot name="belowContent">
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
            {{-- Archive header --}}
            <div class="mb-12">
                <h1 class="font-headline text-4xl md:text-5xl font-bold text-on-surface">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Gazeta personala' : 'Personal gazette' }}</h1>
                <div class="font-label text-xs uppercase tracking-wider mt-2" style="color: var(--color-on-surface-variant);">
                    @php
                        global $wp_query;
                        $total = $wp_query->found_posts ?? 0;
                    @endphp
                    {{ $total }} {{ ICL_LANGUAGE_CODE == 'ro' ? ($total == 1 ? 'articol' : 'articole') : ($total == 1 ? 'entry' : 'entries') }}
                </div>
            </div>

            @include('partials.posts')
            @include('partials.pagination')
        </div>
    </x-slot>
</x-layouts.master>
