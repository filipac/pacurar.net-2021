@extends('layouts.search')

@php
global $orgseries, $wp_query;
remove_filter('the_excerpt', array($orgseries, 'add_series_meta_excerpt'));

function wps_highlight_results($text){
     if(is_search()){
     $sr = get_query_var('s');
     $keys = explode(" ",$sr);
     $text = preg_replace('/('.implode('|', $keys) .')/iu', '<strong class="relevanssi-query-term">'.$sr.'</strong>', $text);
     }
     return $text;
}
// add_filter('the_excerpt', 'wps_highlight_results');
add_filter('the_title', 'wps_highlight_results');

add_action('the_excerpt', function($content) {
    $pos = strpos($content, '<p>');
    $content = substr($content, 0, $pos+3) . '<div class="text-gray-400 inline-block">'.get_the_date('j F Y').' —&nbsp;</div>' . substr($content, $pos+3);
    return $content;
});
@endphp
{{--  --}}
@section('below-content')
@include('partials.search_bar')
    @unless($wp_query->found_posts == 0)
    <div class="px-6 md:px-0">
        {{-- @include('partials.posts') --}}
        @while(have_posts())
            @php
                the_post()
            @endphp
        <a href="{{ the_permalink() }}" class="block dark:text-white dark:border-white border border-black w-full px-2 md:px-12 py-4 md:py-8 shadow-box hover:shadow-boxhvr dark:shadow-box-white dark:hover:shadow-boxhvr-white @unless($loop->first) mt-4 @endunless flex flex-col">
            <div class="text-xs text-gray-400">
                {{ the_permalink() }}
            </div>
            <div class="text-blue-700 dark:text-blue-400 font-bold my-3">
                {{ the_title() }}
            </div>
            <div class="flex">
                <div class="flex-1">
                    {{ the_excerpt() }}
                </div>


            </div>
            <div>@include('partials.tags', ['nolink' => true])</div>
        </a>
        @endwhile
        @include('partials.pagination')
    </div>
    @else
    <div class="flex flex-col items-center mt-4 py-12 px-12">
        <div class="text-6xl uppercase font-bold rotate-6 transform skew-x-6 skew-y-6">Eroare 404!</div>
        <div class="mt-12 text-2xl">Nu am gasit nimic pentru ceea ce incerci tu sa cauti.</div>
        <div class="mt-12 text-2xl">Nu am raspunsul chiar la tot in viata.</div>
    </div>
    @endunless
@overwrite
