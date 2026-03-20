<div class="grid gap-6 grid-cols-1 md:grid-cols-2 mt-4">
    @foreach($items as $item)
        <div class="col-span-1 md:col-span-{{ get_field('colspan', $item) ?: 1 }} pb-4" style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem;">
            @if(has_post_thumbnail($item))
                @php
                    $_img = get_the_post_thumbnail_url( $item, 'full' );
                @endphp
                <div class="featured-image flex sm:max-h-sm sm:max-w-full sm:w-full overflow-hidden" style="border-radius: 0.25rem 0.25rem 0 0;">
                    <img src="{{ $_img }}" class="featured-image sm:max-w-full sm:w-full" loading="lazy" decoding="async" />
                </div>
            @endif
            <div class="p-6">
            <div class="grid items-center flex-wrap grid-cols-1 md:grid-cols-[1fr_max-content]">
                <h2 class="font-headline font-bold mt-4 md:mt-0 text-xl text-center md:text-left">{!! get_the_title($item) !!}</h2>
                @if($type = get_field('type', $item))
                <div class="md:pr-4">
                    <span class="font-label text-xs uppercase tracking-wider px-3 py-1" style="background: var(--color-primary); color: #fff; border-radius: 0.125rem;">{{ $type }}</span>
                </div>
                @endif
            </div>

            <div class="mt-4 text-sm leading-relaxed">
                {!! get_the_content(null, false, $item) !!}
            </div>

            @php
                $categories = get_the_terms( $item, 'technology' ) ?: [];
            @endphp

            @if(count($categories) > 0)
            <div class="mt-3 text-sm" style="color: var(--color-on-surface-variant);">
                Technologies used:
            </div>

            @include('partials.techs')
            @endif

            @if($website = get_field('url', $item))
            <div class="mt-6 w-full text-center">
                <a href="{{ $website }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 font-label text-xs uppercase tracking-wider text-white transition-colors" style="background: var(--color-primary); border-radius: 0.125rem;">
                    Visit project
                </a>
            </div>
            @endif
            </div>

        </div>
    @endforeach
</div>
