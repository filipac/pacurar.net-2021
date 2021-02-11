<div class="grid gap-9 grid-cols-1 md:grid-cols-2 mt-4">
    @foreach($items as $item)
        <div class="post-box bg-white col-span-{{ get_field('colspan', $item) ?: 1 }} shadow-box pb-4">
            @if(has_post_thumbnail($item))
                @php
                    $_img = get_the_post_thumbnail_url( $item, 'full' );
                @endphp
                <div class="featured-image flex sm:max-h-sm sm:max-w-full sm:w-full">
                    {{-- {!! apply_filters('manual_lazy_image', '') !!} --}}
                    <img src="{{ $_img }}" class="featured-image  sm:max-w-full sm:w-full" />
                </div>
            @endif
            <div class="p-6">
            <div class="grid items-center flex-wrap" style="grid-template-columns: 1fr max-content">
                <h2 class="font-bold text-xl">{!! get_the_title($item) !!}</h2>
                @if($type = get_field('type', $item))
                <div class="pr-4">
                    <div
                    class="ultra bg-yellow rounded shadow-box border border-black p-2 text-base transform {{ rand(1,2) == 2 ? '-' : '' }}rotate-12 font-bold text-white"
                    style="background: url({{ get_stylesheet_directory_uri() }}/public/images/noise.png);
                        font-variant: petite-caps;
                        -webkit-text-fill-color: white;
                        -webkit-text-stroke-width: 0.5px;
                        -webkit-text-stroke-color: black;"
                    >{{ $type }}</div>
                </div>
                @endif
            </div>

            <div class="mt-4">
                {!! get_the_content(null, false, $item) !!}
            </div>

            <div class="mt-2">
                Technologies used in this project:
            </div>

            @include('partials.techs')

            @if($website = get_field('url', $item))
            <div class="mt-6 w-full text-center">
                <a href="{{ $website }}" target="_blank" class="group hover:bg-yellow hover:shadow-boxhvr p-2 hover:pb-4 shadow-box border-2 border-black">
                    <span class="relative show-border">Visit project</span>
                </a>
            </div>
            @endif
            </div>

        </div>
    @endforeach
</div>
