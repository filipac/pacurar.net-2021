@extends('layouts.master')

@section('content')
    <!--content here!-->
    @while(have_posts()) @php
        the_post()
    @endphp
    <div class="sigmar text-3xl w-full text-center">
        <p>{{ the_title() }}</p>
    </div>
    @if(has_post_thumbnail())
        <div class='w-full shadow-box border-2 border-black'>
            <img src="{{ get_the_post_thumbnail_url(get_post()->ID, 'full') }}" class="w-full" alt="">
        </div>
    @endif
    <div class="bg-white shadow-box flex-1 w-full border-2 border-black px-4 md:px-32 py-6 md:py-16 text-xl">
        <div class="entry-content pb-20 prose prose-lg max-w-none">
            {!! the_content() !!}
        </div>
    </div>

    <div class="mt-12">
            {!! comments_template('/comments.php') !!}
        </div>
    @endwhile
@endsection
