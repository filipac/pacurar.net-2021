<div class="grid grid-cols-blog-list-mobile xl:grid-cols-blog-list md:gap-4">
    <div class="max-w-full">
        {{ $slot }}
    </div>
    <div class="max-w-full px-4 sm:px-0">
        @include('generic.sidebar')
    </div>
</div>
