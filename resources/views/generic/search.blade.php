@php
    global $orgseries, $wp_query;
    remove_filter('the_excerpt', array($orgseries, 'add_series_meta_excerpt'));

    add_action('the_excerpt', function($content) {
        $pos = strpos($content, '<p>');
        $content = substr($content, 0, $pos+3) . '<div class="font-label text-xs inline-block" style="color: var(--color-on-surface-variant);">'.get_the_date('Y . m . d').' —&nbsp;</div>' . substr($content, $pos+3);
        return $content;
    });
@endphp

<x-layouts.search>
    <x-slot name="belowContent">
        @include('partials.search_bar')
    @unless($wp_query->found_posts == 0)
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
            @php $i = 0; @endphp
            @while(have_posts())
                @php the_post() @endphp
                <a href="{{ the_permalink() }}"
                   class="block w-full px-4 md:px-8 py-6 transition-colors @unless($i === 0) mt-3 @endunless"
                   style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem;">
                    <div class="font-label text-xs" style="color: var(--color-on-surface-variant);">
                        {{ the_permalink() }}
                    </div>
                    <div class="font-headline font-semibold text-primary my-2">
                        {{ the_title() }}
                    </div>
                    <div class="text-sm" style="color: var(--color-on-surface-variant);">
                        {{ the_excerpt() }}
                    </div>
                    <div>@include('partials.tags', ['nolink' => true])</div>
                </a>
            @php $i++; @endphp
            @endwhile
            @include('partials.pagination')
        </div>
    @else
        <div class="flex flex-col items-center py-24 px-8 text-center">
            <div class="font-headline text-6xl font-bold text-on-surface">
                {{ ICL_LANGUAGE_CODE == 'ro' ? 'Eroare 404!' : 'Error 404' }}
            </div>
            <div class="mt-8 text-lg" style="color: var(--color-on-surface-variant);">
                {{ ICL_LANGUAGE_CODE == 'ro' ? 'Nu am gasit nimic pentru ceea ce incerci tu sa cauti.' : 'I did not find anything you\'re searching for.' }}
            </div>
            <div class="mt-4 text-lg" style="color: var(--color-on-surface-variant);">
                {{ ICL_LANGUAGE_CODE == 'ro' ? 'Nu am raspunsul chiar la tot in viata.' : "I don't have the answer to everything in life." }}
            </div>
        </div>
    @endunless
    </x-slot>
</x-layouts.search>
