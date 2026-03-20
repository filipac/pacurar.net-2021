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
            // Capture content early so get_the_content() doesn't interfere with the_content()
            $rawContent = get_post_field('post_content', get_post());
            $wordCount = str_word_count(strip_tags($rawContent));
            $readingTime = max(1, ceil($wordCount / 200));
        @endphp

        <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
            {{-- Title --}}
            @unless(has_post_format('aside'))
                <h1 class="font-headline text-3xl md:text-5xl font-bold text-on-surface mb-8 max-w-4xl">
                    {{ the_title() }}
                </h1>
            @endunless

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                {{-- Metadata sidebar (left) --}}
                <aside class="lg:col-span-3 order-2 lg:order-1">
                    <div class="metadata-sidebar">
                        @unless(has_post_format('aside'))
                            {{-- Date --}}
                            <div class="metadata-section">
                                <div class="metadata-label">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Publicat' : 'Published' }}</div>
                                <div class="metadata-value" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
                                    <span x-show="!hover">{{ get_the_date('Y . m . d') }}</span>
                                    <span x-show="hover" x-cloak>{{ get_the_date('U') }}</span>
                                </div>
                            </div>

                            {{-- Author --}}
                            <div class="metadata-section">
                                <div class="metadata-label">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Autor' : 'Author' }}</div>
                                <div class="metadata-value">{!! app('the_author') !!}</div>
                            </div>

                            {{-- Reading time --}}
                            <div class="metadata-section">
                                <div class="metadata-label">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Timp de citire' : 'Reading time' }}</div>
                                <div class="metadata-value">
                                    ~{{ $readingTime }} min
                                </div>
                            </div>

                            {{-- Tags --}}
                            <div class="metadata-section">
                                <div class="metadata-label">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Etichete' : 'Tags' }}</div>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @php
                                        $categories = get_the_terms(get_post(), 'category') ?: [];
                                        $tags = get_the_tags() ?: [];
                                    @endphp
                                    @foreach($categories as $cat)
                                        <a href="{{ get_term_link($cat) }}" class="metadata-tag">{!! $cat->name !!}</a>
                                    @endforeach
                                    @foreach($tags as $tag)
                                        <a href="{{ get_term_link($tag) }}" class="metadata-tag"># {!! $tag->name !!}</a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Share --}}
                            <div class="metadata-section">
                                <div class="metadata-label">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Distribuie' : 'Share' }}</div>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(get_the_permalink()) }}&text={{ urlencode(get_the_title()) }}" target="_blank" class="share-link">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">share</span>
                                    Twitter
                                </a>
                            </div>
                        @endunless
                    </div>
                </aside>

                {{-- Article content (center) --}}
                <article class="lg:col-span-9 order-1 lg:order-2" style="min-width: 0;">
                    @php
                        $hide_thumbnail = get_post_meta(get_the_ID(), 'hide_thumbnail', true);
                    @endphp
                    @if(has_post_thumbnail() && !$hide_thumbnail)
                        <div class="w-full mb-8 overflow-hidden" style="border-radius: 0.25rem;">
                            <img src="{{ get_the_post_thumbnail_url(get_post()->ID, 'full') }}" class="w-full" alt="" style="object-fit: cover;" fetchpriority="high" decoding="async">
                        </div>
                    @endif

                    @php
                        $xdata = get_post_meta(get_post()->ID, 'x-data', true);
                    @endphp
                    <div class="entry-content pb-12 prose dark:prose-invert {{has_post_format('aside') ? 'prose-xl' : 'prose-lg'}} max-w-none"
                         @if($xdata) x-data='{{ $xdata }}' @endif>
                        {!! the_content() !!}
                    </div>

                    @if(has_post_format('aside'))
                        <div class="mt-2 font-label text-xs" style="color: var(--color-on-surface-variant);">
                            <a href="{{ get_permalink() }}">
                                @include('partials.time')
                            </a>
                        </div>
                        <div class="mt-4">
                            @include('partials.tags')
                        </div>
                    @endif

                    {{-- Comments --}}
                    <div class="mt-12 pt-8" style="border-top: 1px solid var(--color-outline-variant);">
                        {!! comments_template('/comments.php') !!}
                    </div>
                </article>
            </div>
        </div>
    @endwhile
</x-layouts.master>
