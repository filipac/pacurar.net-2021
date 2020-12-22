@if(has_post_thumbnail($_post->wpPost))
    @php
        $_img = get_the_post_thumbnail_url( $_post->wpPost, 'full' );
    @endphp
    <a href="{{ get_the_permalink($_post->wpPost) }}" class="featured-image flex sm:max-h-sm sm:max-w-full sm:w-full">
        {!! apply_filters('manual_lazy_image', '<img src="'.$_img.'" class="featured-image  sm:max-w-full sm:w-full" />') !!}
    </a>
@endif
@include('partials.meta_bar')
<div class="p-6">
<a href="{{ get_the_permalink($_post->wpPost) }}"><h2 class="font-bold text-xl">{!! get_the_title($_post->wpPost) !!}</h2></a>

<div class="mt-2">
    {!! get_the_excerpt($_post->wpPost) !!}
</div>

@include('partials.tags')
</div>
