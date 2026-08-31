<header class="fixed w-full z-50 glass-nav" style="top: 0;" x-data="{ mobileOpen: false }"
    @if(function_exists('is_admin_bar_showing') && is_admin_bar_showing())
        x-init="$el.style.top = window.innerWidth > 782 ? '32px' : '46px'"
    @endif
>
    <nav class="flex justify-between items-center px-4 md:px-8 h-20 max-w-7xl mx-auto">
        {{-- Left: Brand --}}
        <div class="flex items-center gap-3">
            <a href="{{ get_bloginfo('url') }}" class="flex items-center gap-2 no-neon" aria-label="{{ get_bloginfo('name') }}">
                <span style="max-width: 180px;" aria-hidden="true">@include('partials.logo')</span>
            </a>
        </div>

        {{-- Center: Nav links (desktop) --}}
        <div class="hidden md:block">
            {!! wp_nav_menu([
                'theme_location' => 'top_menu',
                'container' => '',
                'items_wrap' => '<ul id="%1$s" class="%2$s navigation flex items-center gap-6">%3$s</ul>',
                'walker' => new App\Classes\Pacurar_Walker,
            ]) !!}
        </div>

        {{-- Right: Dark mode toggle + mobile menu --}}
        <div class="flex items-center gap-3">
            {{-- Geek mode toggle --}}
            <button @click="geekMode = !geekMode; if(geekMode) { darkMode = true; localStorage.setItem('darkMode', 'true'); } localStorage.setItem('geekMode', geekMode)"
                    class="p-2 rounded-lg hover:bg-surface-container transition-colors"
                    :aria-label="geekMode ? 'Disable geek mode' : 'Enable geek mode'"
                    :title="geekMode ? 'Disable geek mode' : 'Enable geek mode'">
                <span class="material-symbols-outlined text-on-surface text-xl" :class="{ 'text-green-400': geekMode }">code</span>
            </button>

            {{-- Dark mode toggle --}}
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); if(!darkMode) { geekMode = false; localStorage.setItem('geekMode', false); }"
                    class="p-2 rounded-lg hover:bg-surface-container transition-colors"
                    :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                <span class="material-symbols-outlined text-on-surface text-xl" x-show="!darkMode">dark_mode</span>
                <span class="material-symbols-outlined text-on-surface text-xl" x-show="darkMode" x-cloak>light_mode</span>
            </button>

            {{-- Mobile menu button --}}
            <button @click="mobileOpen = !mobileOpen"
                    class="md:hidden p-2 rounded-lg hover:bg-surface-container transition-colors"
                    aria-controls="mobile-navigation"
                    :aria-expanded="mobileOpen.toString()"
                    :aria-label="mobileOpen ? 'Close navigation menu' : 'Open navigation menu'">
                <span class="material-symbols-outlined text-on-surface text-xl" x-show="!mobileOpen">menu</span>
                <span class="material-symbols-outlined text-on-surface text-xl" x-show="mobileOpen" x-cloak>close</span>
            </button>
        </div>
    </nav>

    {{-- Mobile nav dropdown --}}
    <div id="mobile-navigation" x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden glass-nav border-t border-outline-variant">
        <div class="px-4 py-4 max-w-7xl mx-auto">
            {!! wp_nav_menu([
                'theme_location' => 'top_menu',
                'container' => '',
                'items_wrap' => '<ul id="%1$s" class="%2$s flex flex-col gap-2">%3$s</ul>',
                'walker' => new App\Classes\Pacurar_Walker,
            ]) !!}
        </div>
    </div>
</header>
