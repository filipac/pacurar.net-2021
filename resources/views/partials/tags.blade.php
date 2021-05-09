<div class="inline-flex flex-wrap flex-gap text-xs mt-4 parentapp" x-data="{show: false}"
 @mouseover="if(jQuery(window).width() > 600) { show = true }" @mouseout="if(jQuery(window).width() > 600) { show = false }"
 @click.away="show = false"
 >
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
    <{{ $tagName }} class="bg-yellow @if(isset($nolink)) dark:bg-gray-800 dark:text-white dark:shadow-box-white dark:hover:shadow-boxhvr-white @endif text-black p-2 shadow-box hover:shadow-boxhvr block prevent-if" href="{{ get_term_link($cat) }}">{!! $cat->name !!}</{{ $tagName }}>
    @endforeach
    @if(count($tags) > 0)
    <{{ $tagName }} class="bg-blue-auto @if(isset($nolink)) dark:bg-gray-600 dark:text-white dark:shadow-box-white dark:hover:shadow-boxhvr-white @endif text-black p-2 shadow-box hover:shadow-boxhvr block prevent-if"
    {{-- :class="{'opacity-0': !show, block: show}" --}}
     href="{{ get_term_link($tags[0]) }}" x-text='show ? "#{{ $tags[0]->name }}" : "#{{ str_repeat(".", strlen($tags[0]->name)) }}"' ></{{ $tagName }}>
    @endif
    @foreach($tags as $tag)
    @if($loop->index == 0)
    @continue
    @endif
    <{{ $tagName }} class="bg-yellow @if(isset($nolink)) dark:bg-gray-600 dark:text-white dark:shadow-box-white dark:hover:shadow-boxhvr-white @endif text-black p-2 shadow-box hover:shadow-boxhvr transition-opacity prevent-if"
    :class="{'opacity-0': !show, block: show}"
     href="{{ get_term_link($tag) }}">#{!! $tag->name !!}</{{ $tagName }}>
    @endforeach
</div>
