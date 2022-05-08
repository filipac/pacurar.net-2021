@if(function_exists('wp_pagenavi'))
    <div class="mt-12 text-center ">
        {!! wp_pagenavi() !!}
    </div>
@endif
