@if(has_post_thumbnail($_post->wpPost))
    @php
        $_img = get_the_post_thumbnail_url( $_post->wpPost, 'full' );
    @endphp
    <a href="{{ get_the_permalink($_post->wpPost) }}" class="featured-image flex sm:max-h-sm sm:max-w-full sm:w-full">
        {!! apply_filters('manual_lazy_image', '<img src="'.$_img.'" class="featured-image  sm:max-w-full sm:w-full" />') !!}
    </a>
@endif
@unless(has_post_format('aside'))
    @include('partials.meta_bar')
@endunless
<div class="p-6">
    @unless(has_post_format('aside'))
        <a href="{{ get_the_permalink($_post->wpPost) }}"><h2
            class="font-bold text-xl">{!! get_the_title($_post->wpPost) !!}</h2></a>
    @endunless

    <div class="mt-2">
        @unless(has_post_format('aside'))
            {!! has_category('story', $_post->wpPost) ? apply_filters( 'the_content', get_the_content(null, false, $_post->wpPost) ) : get_the_excerpt($_post->wpPost) !!}
        @else
        <div class="prose-xl">
            {!! the_content() !!}
        </div>
        @endif
    </div>

    @unless(has_post_format('aside'))
        @include('partials.tags')
    @else
        <div class="mt-2 text-xs">
            <a href="{{ get_permalink() }}">
                @include('partials.time')
            </a>
        </div>
        <div class="flex items-start justify-start">
            <div class="transform scale-50 text-left origin-left">
            @include('partials.tags')
        </div>
        </div>
    @endunless
</div>
