<div class="w-full mx-auto flex flex-col md:flex-row justify-between items-center mb-4">
    <div class="flex items-center" x-data='{show: false}'
    @mouseover="if(jQuery(window).width() > 600) { show = true }"
    @mouseout="if(jQuery(window).width() > 600) { show = false }"
    @click.away="show = false"
 >
        <a href="{{ get_bloginfo('url') }}">
        @includeWhen(!is_search() && !is_page('cauta') && !is_page('search'), 'partials.logo')
        @includeWhen(is_search() || is_page('cauta') || is_page('search'), 'partials.logo_search')
            {{-- @include('partials.logo') --}}
        </a>
        <div class="ml-2 transition-opacity opacity-0" :class='{"opacity-0": !show}'>
            <a href="#" @click.prevent="expandiste()">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </a>
        </div>
    </div>
    {!! wp_nav_menu([
            'theme_location' => 'top_menu',
            'container' => '',
            'items_wrap'           => '<ul id="%1$s" class="%2$s navigation flex">%3$s</ul>',
            'walker' => new App\Classes\Pacurar_Walker,
        ]) !!}
    {{-- <div class="navigation flex">
        <div>
            <a href="/" class="ultra menu-item-text nav-item" @mouseover="$refs.creak.play()">
                Baza
            </a>
        </div>
        <div>
            <a href="/blog" class="ultra menu-item-text nav-item" @mouseover="$refs.creak.play()">
                Blog
            </a>
        </div>
        <div>
            <a href="#" class="ultra menu-item-text nav-item" @mouseover="$refs.creak.play()">
                Uses
            </a>
        </div>
        <div>
            <a href="#" class="ultra menu-item-text nav-item" @mouseover="$refs.creak.play()">
                Despre mine
            </a>
        </div>
        <div>
            <a href="/404" class="ultra menu-item-text nav-item" @mouseover="$refs.creak.play()">
                Arhive
            </a>
        </div>
        <div>
            <a href="/random" class="ultra menu-item-text nav-item" @mouseover="$refs.creak.play()">
                Random
            </a>
        </div>
    </div> --}}
</div>
