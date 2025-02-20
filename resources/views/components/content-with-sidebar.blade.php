<div class="grid grid-cols-(--grid-blog-list-mobile) xl:grid-cols-(--grid-blog-list) md:gap-4">
    <div style="min-width: 0;">
        {{ $slot }}
    </div>
    <div class="max-w-full px-4 sm:px-0">
        @include('generic.sidebar')
    </div>
</div>
