@push('beforeContainer')
    <div class="overflow-hidden">
        <video width="100%" id="rick" class="w-full h-screen fixed" autoplay muted
               style="z-index: -1;object-fit: cover;">
            <source src="{{ public_url('fun/rick.webm') }}" type="video/webm"/>
            </source>
            <source src="{{ public_url('fun/rick.mp4') }}" type="video/mp4"/>
            </source>
            You need an HTML 5-capable browser.
        </video>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('click', function () {
            var element = document.querySelector('#rick')
            if (element) {
                element.muted = false;
                element.volume = 0.2;
            }
        })
    </script>
@endpush

<x-layouts.master title="Selling my blog">
    <x-slot name="belowContent">
        <div class="flex items-center justify-center min-h-[40vh] px-8">
            <section class="flex flex-col text-center max-w-md p-8 md:p-12 relative z-10" style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem; backdrop-filter: blur(8px); background: rgba(243, 250, 255, 0.85);">
                <h1 class="font-headline text-4xl font-bold text-on-surface">JK JK lol!</h1>
                <p class="mt-4 text-sm" style="color: var(--color-on-surface-variant);">While you are here, just read my blog!!</p>
            </section>
        </div>
    </x-slot>
</x-layouts.master>
