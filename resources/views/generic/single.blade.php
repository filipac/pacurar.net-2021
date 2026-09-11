<x-layouts.master>
    @while(have_posts())
        @php
            the_post()
        @endphp

        @push('head')
            @if(($post = get_post()) && ($imgId = get_post_meta($post->ID, 'og_image', true)))
                @php
                    add_filter(
                'wpseo_frontend_presenter_classes',
                function ( $filter ) {
                    if (($key = array_search('Yoast\WP\SEO\Presenters\Open_Graph\Image_Presenter', $filter)) !== false) {
                        unset($filter[$key]);
                    }
                    return $filter;
                }
            );
                @endphp
                @if(($data = wp_get_attachment_image_src($imgId, 'full')) && is_array($data))
                    <meta name="twitter:image" content="{{ $data[0] }}"/>
                    <meta prefix="og: http://ogp.me/ns#" name="image" property="og:image" content="{{ $data[0] }}"/>
                    <meta prefix="og: http://ogp.me/ns#" property="og:url" content="{{ get_the_permalink() }}">
                @endif
            @endif
        @endpush

        @php
            $isAside = has_post_format('aside');
            $isRomanian = ICL_LANGUAGE_CODE == 'ro';
            $rawContent = get_post_field('post_content', get_post());
            $wordCount = preg_match_all('/[\p{L}\p{N}]+/u', wp_strip_all_tags(strip_shortcodes($rawContent)));
            $readingTime = max(1, (int) ceil($wordCount / 200));
            $categories = get_the_terms(get_post(), 'category');
            $categories = is_wp_error($categories) ? [] : ($categories ?: []);
            $tags = get_the_tags() ?: [];
            $showThumbnail = has_post_thumbnail() && !get_post_meta(get_the_ID(), 'hide_thumbnail', true);
            $thumbnailId = $showThumbnail ? get_post_thumbnail_id() : 0;
            $thumbnailDimensions = $thumbnailId ? wp_get_attachment_image_src($thumbnailId, 'full') : false;
            $isPortraitThumbnail = $thumbnailDimensions && $thumbnailDimensions[1] > 0
                && $thumbnailDimensions[2] > $thumbnailDimensions[1];
            $xdata = get_post_meta(get_the_ID(), 'x-data', true);
        @endphp

        <div class="journal-single {{ $isAside ? 'journal-single-note' : '' }} max-w-7xl mx-auto px-4 md:px-8 py-10 md:py-14">
            <div class="reading-intro {{ $isPortraitThumbnail ? 'reading-intro-portrait' : '' }}">
                <div class="reading-header">
                    <a href="{{ home_url('/blog') }}" class="reading-back">
                        <span aria-hidden="true">←</span>
                        {{ $isRomanian ? 'Înapoi la blog' : 'Back to the blog' }}
                    </a>

                    <div class="reading-categories flex flex-wrap items-center gap-2">
                        @if($isAside)
                            <span class="post-category">{{ $isRomanian ? 'Pe scurt' : 'A quick note' }}</span>
                        @else
                            @foreach($categories as $category)
                                <a href="{{ get_term_link($category) }}" class="post-category">{!! $category->name !!}</a>
                            @endforeach
                        @endif
                    </div>

                    @if($isAside)
                        <h1 id="reading-title" class="sr-only">{{ ($isRomanian ? 'Însemnare din ' : 'A note from ') . get_the_date() }}</h1>
                    @else
                        <h1 id="reading-title" class="reading-title font-headline text-4xl md:text-6xl font-bold text-on-surface">
                            {!! get_the_title() !!}
                        </h1>
                    @endif

                    <div class="reading-meta flex flex-wrap items-center gap-x-5 gap-y-3">
                        <span class="reading-author">{!! app('the_author') !!}</span>
                        <time datetime="{{ get_the_date('c') }}" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
                            <span x-show="!hover">{{ get_the_date($isAside ? 'j M Y · H:i' : 'j M Y') }}</span>
                            <span x-show="hover" x-cloak>{{ get_the_date('U') }}</span>
                        </time>
                        @unless($isAside)
                            <span>{{ $readingTime }} {{ $isRomanian ? 'min de citit' : 'min read' }}</span>
                        @endunless
                    </div>
                </div>

                @if($isPortraitThumbnail)
                    @include('partials.posts.featured-image')
                @endif
            </div>

            <article class="reading-article" aria-labelledby="reading-title">
                @if($showThumbnail && !$isPortraitThumbnail)
                    @include('partials.posts.featured-image')
                @endif

                <div class="reading-paper {{ $showThumbnail && !$isPortraitThumbnail ? 'reading-paper-with-cover' : '' }}">
                    <div class="entry-content prose dark:prose-invert prose-lg max-w-none"
                         @if($xdata) x-data='{{ $xdata }}' @endif>
                        {!! the_content() !!}
                    </div>

                    {!! wp_link_pages(['before' => '<nav class="reading-pages" aria-label="'.esc_attr($isRomanian ? 'Paginile articolului' : 'Article pages').'">', 'after' => '</nav>', 'echo' => false]) !!}

                    <div class="reading-end">
                        @if($isAside && $categories)
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($categories as $category)
                                    <a href="{{ get_term_link($category) }}" class="post-category">{!! $category->name !!}</a>
                                @endforeach
                            </div>
                        @endif
                        @if($tags)
                            <div class="reading-tags flex flex-wrap gap-2 mb-6">
                                @foreach($tags as $tag)
                                    <a href="{{ get_term_link($tag) }}">#{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <a href="{{ home_url('/blog') }}" class="reading-back">
                                <span aria-hidden="true">←</span>
                                {{ $isRomanian ? 'Mai multe din jurnal' : 'More from the journal' }}
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(get_the_permalink()) }}&text={{ urlencode(wp_strip_all_tags(get_the_title())) }}" target="_blank" rel="noopener noreferrer" class="reading-share">
                                <span class="material-symbols-outlined" aria-hidden="true">share</span>
                                {{ $isRomanian ? 'Distribuie pe Twitter' : 'Share on Twitter' }}
                            </a>
                        </div>
                    </div>
                </div>
            </article>

            <section class="reading-comments" aria-label="{{ $isRomanian ? 'Conversația' : 'The conversation' }}">
                <div class="reading-comments-intro">
                    <span class="reading-discussion-mark" aria-hidden="true">✳</span>
                    <p class="eyebrow">{{ $isRomanian ? 'De aici, continuăm împreună.' : 'The conversation continues here.' }}</p>
                </div>
                {!! comments_template('/comments.php') !!}
            </section>
        </div>
    @endwhile
</x-layouts.master>
