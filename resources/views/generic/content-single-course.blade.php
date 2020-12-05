@extends('layouts.master')

@section('content')
    @while(have_posts()) @php
        the_post()
    @endphp
<div class="sigmar text-3xl w-full text-center text-shadow">
            <p>{{ the_title() }}</p>
        </div>
        @if(has_post_thumbnail())
        <div class='w-full shadow-box border-2 border-black'>
            <img src="{{ get_the_post_thumbnail_url(get_post()->ID, 'full') }}" class="w-full" alt="">
        </div>
        @endif
        <div class="bg-white shadow-box flex-1 w-full border-2 border-black px-2 md:px-32 py-16 text-xl">
            <div class="entry-content c-rich-text pb-20">
                {!! the_content() !!}
            </div>
        </div>
    @endwhile
@endsection
