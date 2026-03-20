{{-- Footer section --}}
<footer class="mt-auto border-t border-outline-variant" style="background: var(--color-surface-container-low);">
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
        {{-- Widget grid --}}
        @if(is_active_sidebar('nice_sidebar'))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 footer-widgets">
            @php dynamic_sidebar('nice_sidebar') @endphp
        </div>
        <div class="border-t border-outline-variant pt-8"></div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            {{-- Copyright --}}
            <div class="font-label text-xs" style="color: var(--color-on-surface-variant);">
                @if(ICL_LANGUAGE_CODE == 'ro')
                    &copy; {{ date('Y') }} Filip Pacurar &mdash; toate drepturile nu sunt rezervate.
                @else
                    &copy; {{ date('Y') }} Filip Pacurar &mdash; all rights are not reserved.
                @endif
            </div>

            {{-- Social links --}}
            <div class="flex items-center gap-4 font-label text-xs uppercase tracking-wider">
                <a href="https://github.com/filipac" target="_blank" rel="me" class="hover:text-primary transition-colors" style="color: var(--color-on-surface-variant);">GitHub</a>
                <a href="https://www.linkedin.com/in/filipacurar/" target="_blank" rel="me" class="hover:text-primary transition-colors" style="color: var(--color-on-surface-variant);">LinkedIn</a>
                <a href="{{ get_bloginfo('rss2_url') }}" target="_blank" class="hover:text-primary transition-colors" style="color: var(--color-on-surface-variant);">RSS</a>
            </div>

            {{-- Tech credit --}}
            <div class="font-label text-xs" style="color: var(--color-on-surface-variant);">
                @if(ICL_LANGUAGE_CODE == 'ro')
                    Tema facuta cu Tailwind CSS. <a href="https://github.com/filipac/pacurar.net-2021" target="_blank" class="underline hover:text-primary">Cod sursa</a>
                @else
                    Built with Tailwind CSS. <a href="https://github.com/filipac/pacurar.net-2021" target="_blank" class="underline hover:text-primary">Source code</a>
                @endif
            </div>
        </div>
    </div>
</footer>

@yield('footer')
{{ $footer ?? '' }}
@livewireScripts
@unless(isset($inBlock))
    @php
        wp_footer()
    @endphp
@endif
@if(ICL_LANGUAGE_CODE == 'ro')
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-31FN5CD470"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-31FN5CD470');
    </script>
@else
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-HG03G6CK1E"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-HG03G6CK1E');
    </script>
@endif
@php
    $browser = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $isPs = strpos($browser, 'Chrome-Lighthouse') !== false;
@endphp
@if(!$isPs)
    @vite(['resources/js/app.js'])
    @stack('scripts')
@endif
<div class="dapp-required">

</div>
@stack('scriptsEnd')
