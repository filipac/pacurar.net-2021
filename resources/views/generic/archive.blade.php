@php
global $wp_filter;
@endphp
<x-layouts.master>
    <x-slot name="belowContent">
        @inject('helper', 'Yoast\WP\SEO\Helpers\Current_Page_Helper')
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
            {{-- Archive header --}}
            <div class="mb-12">
                <h1 class="font-headline text-4xl md:text-5xl font-bold text-on-surface">
                    {{ is_category() ? single_cat_title('', false) : (is_tag() ? single_tag_title('', false) : (is_author() ? get_the_author() : (ICL_LANGUAGE_CODE == 'ro' ? 'Arhiva' : 'Archive'))) }}
                </h1>
                @if(is_category() || is_tag())
                    <div class="font-label text-xs uppercase tracking-wider mt-2" style="color: var(--color-on-surface-variant);">
                        @php
                            global $wp_query;
                            $total = $wp_query->found_posts ?? 0;
                        @endphp
                        {{ $total }} {{ ICL_LANGUAGE_CODE == 'ro' ? ($total == 1 ? 'articol' : 'articole') : ($total == 1 ? 'entry' : 'entries') }}
                    </div>
                @endif
            </div>

            @include('partials.posts')
            @include('partials.pagination')
        </div>
    </x-slot>
</x-layouts.master>
