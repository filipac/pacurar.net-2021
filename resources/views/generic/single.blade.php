<x-layouts.master>
    <x-slot name="belowContent">
        @include('partials.copy')
    </x-slot>
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
        @unless(has_post_format('aside'))
            <div class="sigmar text-3xl w-full text-black text-center text-shadow my-6 md:my-2 hidden lg:block">
                <p>{{ the_title() }}</p>
            </div>
        @endunless

        <x-content-with-sidebar>
            @php
                $hide_thumbnail = get_post_meta(get_the_ID(), 'hide_thumbnail', true);
            @endphp
            @if(has_post_thumbnail() && !$hide_thumbnail)
                <div class='w-full shadow-box border-2 border-black'>
                    <img src="{{ get_the_post_thumbnail_url(get_post()->ID, 'full') }}" class="w-full" alt="">
                </div>
            @endif
            @unless(has_post_format('aside'))
                @include('partials.meta_bar')
            @endunless
            <div class="bg-white shadow-box flex-1 w-full border-2 border-black">
                <div class="px-2 md:px-32 py-8 md:py-16 text-xl">
                    <div class="sigmar text-3xl text-black w-full text-center text-shadow my-6 md:my-2 block lg:hidden">
                        <p>{{ the_title() }}</p>
                    </div>
                    @unless(has_post_format('aside'))
                        <div class="pb-6">
                            @include('partials.tags')
                        </div>
                    @endunless
                    @php
                        $xdata = get_post_meta(get_post()->ID, 'x-data', true);
                    @endphp
                    <div
                        class="entry-content pb-20 prose {{has_post_format('aside') ? 'prose-2xl' : 'prose-lg'}} max-w-none"
                        @if($xdata) x-data='{{ $xdata }}' @endif>
                        {!! the_content() !!}
                    </div>
                    @if(has_post_format('aside'))
                        <div class="mt-2 text-xs">
                            <a href="{{ get_permalink() }}">
                                @include('partials.time')
                            </a>
                        </div>
                    @endif
                    @if(has_post_format('aside'))
                        <div class="pb-6">
                            @include('partials.tags')
                        </div>
                    @endif
                </div>


                <div class="mt-4">
                    <x-web3-ad spaceName="single-bottom-1" format="dark" />
                </div>
            </div>

            <div class="mt-12">
                {!! comments_template('/comments.php') !!}
            </div>
        </x-content-with-sidebar>
    @endwhile
</x-layouts.master>
