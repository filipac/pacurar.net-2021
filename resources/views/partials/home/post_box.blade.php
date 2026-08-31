@php
    $hasThumbnail = has_post_thumbnail($_post->wpPost);
    $hideThumbnail = get_post_meta($_post->wpPost->ID ?? $_post->wpPost, 'hide_thumbnail', true);
    $showThumb = $hasThumbnail && !$hideThumbnail;
    $postTitle = wp_strip_all_tags(get_the_title($_post->wpPost));
    $readLabel = sprintf(ICL_LANGUAGE_CODE == 'ro' ? 'Citește %s' : 'Read %s', $postTitle);
@endphp
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start group">
    {{-- Date column --}}
    <div class="md:col-span-2 font-label text-xs uppercase tracking-wider pt-1" style="color: var(--color-on-surface-variant);" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
        <span x-show="!hover">{{ get_the_date('Y . m . d', $_post->wpPost) }}</span>
        <span x-show="hover" x-cloak>{{ get_the_date('U', $_post->wpPost) }}</span>
    </div>

    {{-- Title + excerpt --}}
    <div class="{{ $showThumb ? 'md:col-span-7' : 'md:col-span-9' }}">
        @unless(has_post_format('aside'))
            <a href="{{ get_the_permalink($_post->wpPost) }}" class="block">
                <h2 class="font-headline text-xl md:text-2xl font-semibold text-on-surface group-hover:text-primary transition-colors">
                    {!! get_the_title($_post->wpPost) !!}
                </h2>
            </a>
        @endunless

        <div class="mt-2 text-sm leading-relaxed" style="color: var(--color-on-surface-variant);">
            @unless(has_post_format('aside'))
                <a href="{{ get_the_permalink($_post->wpPost) }}" class="block" style="color: inherit;">
                    {!! has_category('story', $_post->wpPost) ? apply_filters( 'the_content', get_the_content(null, false, $_post->wpPost) ) : get_the_excerpt($_post->wpPost) !!}
                </a>
            @else
                <div class="prose dark:prose-invert prose-sm max-w-none">
                    {!! the_content() !!}
                </div>
            @endif
        </div>

        @unless(has_post_format('aside'))
            @include('partials.tags')
        @else
            <div class="mt-2 font-label text-xs" style="color: var(--color-on-surface-variant);">
                <a href="{{ get_permalink() }}">@include('partials.time')</a>
            </div>
            <div class="mt-2">@include('partials.tags')</div>
        @endunless
    </div>

    {{-- Thumbnail --}}
    @if($showThumb)
        <div class="md:col-span-2 hidden md:block">
            <a href="{{ get_the_permalink($_post->wpPost) }}" class="block overflow-hidden" style="border-radius: 0.25rem;" aria-label="{{ $readLabel }}">
                {!! wp_get_attachment_image(
                    get_post_thumbnail_id($_post->wpPost),
                    'medium',
                    false,
                    [
                        'alt' => $postTitle,
                        'class' => 'w-full object-cover group-hover:scale-105 transition-transform duration-300',
                        'style' => 'aspect-ratio: 4/3;',
                        'loading' => 'lazy',
                        'decoding' => 'async',
                        'sizes' => '(min-width: 1280px) 190px, (min-width: 768px) 16vw, 0px',
                    ]
                ) !!}
            </a>
        </div>
    @endif

    {{-- Arrow link --}}
    <div class="md:col-span-1 hidden md:flex items-center justify-end pt-1">
        @unless(has_post_format('aside'))
        <a href="{{ get_the_permalink($_post->wpPost) }}" class="text-on-surface-variant group-hover:text-primary transition-colors" aria-label="{{ $readLabel }}">
            <span class="material-symbols-outlined" style="font-size: 20px;" aria-hidden="true">arrow_forward</span>
        </a>
        @endunless
    </div>
</div>
