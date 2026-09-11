@php
    $wpPost = $_post->wpPost();
    $isAside = has_post_format('aside', $wpPost);
    $isStory = has_category('story', $wpPost);
    $showThumb = has_post_thumbnail($wpPost) && !get_post_meta($wpPost->ID, 'hide_thumbnail', true) && !$isAside && !$isStory;
    $postTitle = wp_strip_all_tags(get_the_title($wpPost));
    $permalink = get_the_permalink($wpPost);
    $isRomanian = ICL_LANGUAGE_CODE == 'ro';
    $readLabel = sprintf($isRomanian ? 'Citește %s' : 'Read %s', $postTitle);
    $categories = get_the_terms($wpPost, 'category');
    $category = !is_wp_error($categories) && $categories ? reset($categories) : null;
@endphp
<article class="journal-post {{ $isAside ? 'journal-post-note' : '' }} {{ $isStory ? 'journal-post-story' : '' }} {{ $showThumb ? 'journal-post-illustrated' : 'journal-post-text' }}">
    @if($showThumb)
        <a href="{{ $permalink }}" class="post-cover" aria-label="{{ $readLabel }}">
            {!! wp_get_attachment_image(
                get_post_thumbnail_id($wpPost),
                'large',
                false,
                [
                    'alt' => $postTitle,
                    'class' => 'post-cover-image',
                    'loading' => 'lazy',
                    'decoding' => 'async',
                    'sizes' => '(min-width: 1280px) 595px, (min-width: 768px) calc((100vw - 6rem) / 2), calc(100vw - 2rem)',
                ]
            ) !!}
        </a>
    @endif

    <div class="post-body">
        <div class="post-meta flex flex-wrap items-center gap-3">
            @if($isAside)
                <span class="post-category">{{ $isRomanian ? 'Pe scurt' : 'A quick note' }}</span>
            @elseif($category)
                <a href="{{ get_term_link($category) }}" class="post-category">{!! $category->name !!}</a>
            @endif
            <time datetime="{{ get_the_date('c', $wpPost) }}" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
                <span x-show="!hover">{{ get_the_date('j M Y', $wpPost) }}</span>
                <span x-show="hover" x-cloak>{{ get_the_date('U', $wpPost) }}</span>
            </time>
        </div>

        @unless($isAside)
            <h2 class="post-title font-headline text-2xl md:text-3xl font-bold">
                <a href="{{ $permalink }}">{!! get_the_title($wpPost) !!}</a>
            </h2>
        @endunless

        @if($isAside)
            <div class="post-note-content prose dark:prose-invert max-w-none">
                {!! the_content() !!}
            </div>
        @elseif($isStory)
            <div class="post-story-content">
                {!! apply_filters('the_content', get_the_content(null, false, $wpPost)) !!}
            </div>
        @else
            <div class="post-excerpt">{!! get_the_excerpt($wpPost) !!}</div>
        @endif

        @php($postTags = get_the_tags($wpPost) ?: [])
        @if($postTags)
            <div class="post-tags flex flex-wrap gap-x-3 gap-y-2">
                @foreach($postTags as $tag)
                    <a href="{{ get_term_link($tag) }}">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif

        <div class="post-footer flex items-center justify-between gap-4">
            <a href="{{ $permalink }}" class="post-read-link" aria-label="{{ $readLabel }}">
                {{ $isAside ? ($isRomanian ? 'Vezi însemnarea' : 'View note') : ($isRomanian ? 'Citește mai departe' : 'Continue reading') }}
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            </a>
            @php($commentCount = get_comments_number($wpPost))
            @if($commentCount > 0)
                <a href="{{ get_comments_link($wpPost) }}" class="post-comments">
                    {{ $commentCount }} {{ $isRomanian ? ($commentCount == 1 ? 'comentariu' : 'comentarii') : ($commentCount == 1 ? 'comment' : 'comments') }}
                </a>
            @endif
        </div>
    </div>
</article>
