<div class="inline-flex flex-wrap gap-1 text-xs mt-3">
    @php
        $p = isset($_post) ? $_post->wpPost() : get_post();
        $tags = get_the_tags($p) ?: [];
        $categories = get_the_terms( $p, 'category' ) ?: [];

        $tagName = 'a';
        if(isset($nolink)) {
            $tagName = 'div';
        }
    @endphp

    @foreach($categories as $cat)
    <{{ $tagName }} class="font-label uppercase tracking-wider px-2 py-1 transition-colors" style="background: var(--color-surface-container); color: var(--color-on-surface); border-radius: 0.125rem; font-size: 0.625rem;" href="{{ get_term_link($cat) }}">{!! $cat->name !!}</{{ $tagName }}>
    @endforeach
    @foreach($tags as $tag)
    <{{ $tagName }} class="font-label uppercase tracking-wider px-2 py-1 transition-colors" style="background: var(--color-surface-container-low); color: var(--color-on-surface-variant); border-radius: 0.125rem; font-size: 0.625rem;" href="{{ get_term_link($tag) }}"># {!! $tag->name !!}</{{ $tagName }}>
    @endforeach
</div>
