<x-layouts.master>
    <div class="max-w-4xl mx-auto px-4 md:px-8 py-12">
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
                <div class="w-full mb-8 overflow-hidden" style="border-radius: 0.25rem;">
                    <img src="{{ get_the_post_thumbnail_url(get_post()->ID, 'full') }}" class="w-full" alt="" style="object-fit: cover;">
                </div>
            @endif

            @unless(get_post_meta(get_the_ID(), 'no-title', true) == '1')
                <h1 class="font-headline text-3xl md:text-5xl font-bold text-on-surface mb-8">
                    {{ the_title() }}
                </h1>
            @endunless

            <div class="entry-content prose dark:prose-invert prose-lg max-w-none">
                {!! the_content() !!}
            </div>

            <div class="mt-12">
                {!! comments_template('/comments.php') !!}
            </div>
        @endwhile
    </div>
</x-layouts.master>
