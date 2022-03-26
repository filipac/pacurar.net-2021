<div class="@if(is_single()) border-2 border-black shadow-box bg-splash text-white @else border-b-2 border-t-2 hover:shadow-box border-black bg-yellow  @endif py-3  flex flex-col md:flex-row flex-wrap items-center justify-center transition-height px-2 text-xs">
    <div class="">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Scris pe' : 'Wrote on' }}
        <div class="inline-block"
        x-data="{geek: false}"
        @mouseover="geek = true;"
        @mouseout="geek = false;"
        :class='{"bit-rotated": geek}'
        x-text='geek ? "{{ get_the_date('U') }}" : "{{ get_the_date('j F Y'.(is_single() ? ' H:i' : '')) }}"'></div>
    </div>
    <div class=" hidden md:block md:ml-2">//</div>
    <div class=" md:ml-2 ">
        {!! app('the_author') !!}
        {{-- {!! get_the_author_posts_link() !!} --}}
    </div>
</div>
