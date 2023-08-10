@push('beforeContainer')
    <div class="overflow-hidden">
        {{-- <iframe width="560" height="315" class="w-full h-screen fixed skip"
        style="z-index: -1;object-fit: cover;"
        src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?controls=0&autoplay=1&disablekb=1&fs=0&loop=1&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> --}}
        <video width="100%" id="rick" class="w-full h-screen fixed skip" autoplay muted
               style="z-index: -1;object-fit: cover;">
            <!-- HTML 5 browsers will play one of these -->
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
        jQuery(document).on('click', function () {
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
        <div class="m-8">
            <div class="flex items-center justify-center">
                <section
                        class="flex flex-col text-center bg-splash text-white w-auto p-12 shadow-box hover:shadow-boxhvr perspective-sm">
                    <div>
                        <h1 class="text-4xl">JK JK lol!</h1>
                        <p>While you are here, just read my blog!!</p>
                    </div>
                </section>
            </div>
        </div>
    </x-slot>
</x-layouts.master>
