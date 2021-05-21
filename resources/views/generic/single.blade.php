@extends('layouts.master')

@section('content')
    @while(have_posts()) @php
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
        <meta name="image" property="og:image" content="{{ $data[0] }}"/>
        @endif
    @endif
    @endpush
<div class="sigmar text-3xl w-full text-center text-shadow my-6 md:my-2 hidden lg:block">
            <p>{{ the_title() }}</p>
        </div>
        @if(has_post_thumbnail())
        <div class='w-full shadow-box border-2 border-black'>
            <img src="{{ get_the_post_thumbnail_url(get_post()->ID, 'full') }}" class="w-full" alt="">
        </div>
        @endif
        @include('partials.meta_bar')
        <div class="bg-white shadow-box flex-1 w-full border-2 border-black px-2 md:px-32 py-8 md:py-16 text-xl">
            <div class="sigmar text-3xl w-full text-center text-shadow my-6 md:my-2 block lg:hidden">
            <p>{{ the_title() }}</p>
        </div>
            <div class="pb-6">
                @include('partials.tags')
            </div>
            @php
                $xdata = get_post_meta(get_post()->ID, 'x-data', true);
            @endphp
            <div class="entry-content pb-20 prose prose-lg max-w-none" @if($xdata) x-data='{{ $xdata }}' @endif>
                {!! the_content() !!}
            </div>
        </div>

        <div class="mt-12">
            {!! comments_template('/comments.php') !!}
        </div>
    @endwhile
@endsection

@section('below-content')
    @include('partials.copy')
@overwrite
