<div class="@if(is_single()) border-2 border-black shadow-box bg-splash text-white @else border-b-2 border-t-2 hover:shadow-box border-black bg-primary  @endif py-3  flex flex-col md:flex-row flex-wrap items-center justify-center transition-height px-2 text-xs">
    @include('partials.time')
    <div class=" hidden md:block md:ml-2">//</div>
    <div class=" md:ml-2 ">
        {!! app('the_author') !!}
        {{-- {!! get_the_author_posts_link() !!} --}}
    </div>
</div>
