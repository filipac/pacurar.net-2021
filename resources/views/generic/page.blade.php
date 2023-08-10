<x-layouts.master>
    <x-slot name="belowContent">@include('partials.copy')</x-slot>
    <x-content-with-sidebar>
        <!--content here!-->
            @while(have_posts())
                @php
                    the_post()
                @endphp
                @push('head')
                    @if(($post = get_post()) && ($imgId = get_post_meta($post->ID, 'og_image', true)))
                        @if(($data = wp_get_attachment_image_src($imgId, 'full')) && is_array($data))
                            <meta name="og:image" content="{{ $data[0] }}"/>
                        @endif
                    @endif
                @endpush
                @if(has_post_thumbnail())
                    <div class='w-full shadow-box border-2 border-black'>
                        <img src="{{ get_the_post_thumbnail_url(get_post()->ID, 'full') }}" class="w-full" alt="">
                    </div>
                @endif
                <div class="bg-white shadow-box flex-1 w-full border-2 border-black px-4 md:px-32 text-xl">
                    <div class="sigmar text-3xl w-full text-center pt-4">
                        <p>{{ the_title() }}</p>
                    </div>
                    <div class="entry-content pb-20 prose prose-lg max-w-none py-4 md:py-8">
                        {!! the_content() !!}
                    </div>
                </div>

                <div class="mt-12">
                    {!! comments_template('/comments.php') !!}
                </div>
            @endwhile
    </x-content-with-sidebar>
</x-layouts.master>
