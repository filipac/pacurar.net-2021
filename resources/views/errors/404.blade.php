@push('beforeContainer')
    <div class="overflow-hidden">
        <video width="100%" id="rick" class="w-full h-screen fixed" autoplay muted style="z-index: 0;object-fit: cover;">
            <source src="{{ public_url('fun/rick.webm') }}" type="video/webm" /></source>
            <source src="{{ public_url('fun/rick.mp4') }}" type="video/mp4" /></source>
            You need an HTML 5-capable browser.
        </video>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('click', function() {
            var element = document.querySelector('#rick')
            if(element) {
                element.muted = false;
                element.volume = 0.2;
            }
        })
    </script>
@endpush

<x-layouts.master>
    <x-slot name="belowContent">
    <div class="flex items-center justify-center min-h-[60vh] px-8">
        <section class="flex flex-col text-center max-w-lg p-8 md:p-12 relative z-10" style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem; backdrop-filter: blur(8px); background: rgba(243, 250, 255, 0.85);">
            <img src="{{ public_url('fun/bad_vibes.gif') }}" alt="" class="mx-auto mb-6" style="border-radius: 0.25rem; max-width: 200px;" loading="lazy" decoding="async">
            <div>
                @if(ICL_LANGUAGE_CODE == 'ro')
                <h1 class="font-headline text-5xl font-bold text-on-surface">404</h1>
                <p class="mt-4 text-sm" style="color: var(--color-on-surface-variant);">Pagina pe care o cauti nu exista.<br/>
                Dar m-am gandit ca iti va placea acest video mirobolant.<br />
                Click oriunde in pagina sa porneasca si audio.</p>
                @else
                <h1 class="font-headline text-5xl font-bold text-on-surface">404</h1>
                <p class="mt-4 text-sm" style="color: var(--color-on-surface-variant);">The page you are looking for does not exist.<br/>
                But... I thought you would like this awesome video.<br />
                Click anywhere on the page so the audio starts too.</p>
                @endif
            </div>
            <div class="mt-6">
                <a href="{{home_url()}}" class="inline-block px-6 py-3 font-label text-xs uppercase tracking-wider text-white transition-colors" style="background: var(--color-primary);">
                    @if(ICL_LANGUAGE_CODE == 'ro')
                        Inapoi la baza
                    @else
                        Back to home
                    @endif
                </a>
            </div>
        </section>
    </div>
    </x-slot>
</x-layouts.master>
