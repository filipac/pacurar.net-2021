@php
    if (request()->boolean('spotify_refresh')) {
        \Cache::forget('spotify_play');
    }

    $data = \Cache::remember('spotify_play', \Carbon\Carbon::now()->addMinutes(3), function() {
        $api = new \SpotifyWebAPI\SpotifyWebAPI([
            'auto_refresh' => true,
        ]);
        $session = \App\SpotifySession::get();
        $api->setSession($session);

        try {
            $now = $api->getMyCurrentTrack([
                'additional_types' => 'track,episode',
            ]);
            update_option('spotify_token', $session->getAccessToken());
            update_option('spotify_refresh_token', $session->getRefreshToken());
            update_option('spotify_expires', $session->getTokenExpiration());

            return $now;
        } catch (\SpotifyWebAPI\SpotifyWebAPIException $e) {
            // dump($e);
        }

        return (object) ['is_playing' => false];
    });

    $isPlaying = $data && isset($data->item) && $data->is_playing;
    $item = $isPlaying ? $data->item : null;
    $imageUrl = $item
        ? (data_get($item, 'album.images.0.url') ?? data_get($item, 'images.0.url'))
        : null;
    $isEpisode = $item && isset($item->show);
    $spotifyUrl = $item
        ? (data_get($item, 'external_urls.spotify') ?? 'https://open.spotify.com/'.($isEpisode ? 'episode' : 'track').'/'.$item->id)
        : 'https://open.spotify.com/';
    $artistNames = $item && isset($item->artists)
        ? collect($item->artists)->pluck('name')->implode(', ')
        : null;
    $contextName = $isEpisode ? data_get($item, 'show.name') : $artistNames;
    $contextDetail = $isEpisode ? data_get($item, 'show.publisher') : null;
@endphp

<section
    class="relative h-full overflow-hidden p-5 text-white sm:p-7 md:p-8"
    style="background: linear-gradient(135deg, #18271e 0%, #101713 48%, #090b0a 100%); border: 1px solid rgba(29, 185, 84, 0.35); border-radius: 0.5rem; box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);"
    aria-label="{{ ICL_LANGUAGE_CODE == 'ro' ? 'Ce ascult acum pe Spotify' : 'What I am listening to on Spotify' }}"
>
    @if($isPlaying)
        <div class="mx-auto grid max-w-5xl grid-cols-1 items-center gap-6 md:grid-cols-12 md:gap-10">
            <a
                href="{{ $spotifyUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="group mx-auto block w-full max-w-sm md:col-span-5 md:max-w-none lg:col-span-4"
                aria-label="{{ ICL_LANGUAGE_CODE == 'ro' ? 'Deschide pe Spotify' : 'Open on Spotify' }}: {{ $item->name }}"
            >
                <span class="block overflow-hidden" style="border-radius: 0.375rem; box-shadow: 0 22px 50px rgba(0, 0, 0, 0.42);">
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $item->name }}"
                        class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        loading="lazy"
                        decoding="async"
                    >
                </span>
            </a>

            <div class="min-w-0 text-center md:col-span-7 md:text-left lg:col-span-8">
                <div class="mb-6 flex flex-wrap items-center justify-center gap-3 md:justify-between">
                    <div class="inline-flex items-center gap-2 font-label text-xs uppercase tracking-wider" style="color: #a7f3c0;">
                        <span class="inline-block h-2 w-2 rounded-full" style="background: #1db954; box-shadow: 0 0 0 4px rgba(29, 185, 84, 0.15);"></span>
                        {{ ICL_LANGUAGE_CODE == 'ro' ? 'Acum ascult' : 'Now listening' }}
                    </div>

                    <div class="inline-flex items-center gap-2 font-headline text-lg font-semibold" style="color: #1ed760;">
                        <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 224 224" fill="currentColor">
                            <path d="M177.7 99c-36-21.4-95.4-23.3-129.7-12.9a10.4 10.4 0 1 1-6.1-19.9c39.4-12 105-9.7 146.4 14.9a10.4 10.4 0 1 1-10.6 17.9Z"/>
                            <path d="M176.5 130.6a8.7 8.7 0 0 1-12 2.9c-30-18.5-75.7-23.8-111.2-13a8.7 8.7 0 1 1-5.1-16.7c40.6-12.3 91-6.3 125.5 14.8a8.7 8.7 0 0 1 2.8 12Z"/>
                            <path d="M162.9 161a7 7 0 0 1-9.6 2.3c-26.2-16-59.2-19.6-98.1-10.7a7 7 0 1 1-3.1-13.6c42.5-9.7 79-5.5 108.5 12.5a7 7 0 0 1 2.3 9.5Z"/>
                            <path fill-rule="evenodd" d="M111.7 0a111.7 111.7 0 1 0 0 223.3 111.7 111.7 0 0 0 0-223.3Zm0 13.7a98 98 0 1 0 0 196 98 98 0 0 0 0-196Z" clip-rule="evenodd"/>
                        </svg>
                        <span>Spotify</span>
                    </div>
                </div>

                <div class="font-label text-xs uppercase tracking-wider" style="color: rgba(255, 255, 255, 0.55);">
                    {{ $isEpisode ? (ICL_LANGUAGE_CODE == 'ro' ? 'Podcast' : 'Podcast') : (ICL_LANGUAGE_CODE == 'ro' ? 'Piesă' : 'Track') }}
                </div>

                <h2 class="mt-3 break-words font-headline text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                    {{ $item->name }}
                </h2>

                @if($contextName)
                    <p class="mt-4 text-base leading-relaxed sm:text-lg" style="color: rgba(255, 255, 255, 0.76);">
                        {{ $contextName }}
                        @if($contextDetail)
                            <span class="block text-sm" style="color: rgba(255, 255, 255, 0.5);">
                                {{ $contextDetail }}
                            </span>
                        @endif
                    </p>
                @endif

                <a
                    href="{{ $spotifyUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-7 inline-flex items-center gap-2 px-5 py-3 font-label text-xs font-semibold uppercase tracking-wider text-black transition-transform hover:-translate-y-0.5"
                    style="background: #1ed760; border-radius: 9999px; box-shadow: 0 10px 25px rgba(29, 185, 84, 0.22);"
                >
                    {{ ICL_LANGUAGE_CODE == 'ro' ? 'Ascultă pe Spotify' : 'Listen on Spotify' }}
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 14 14 6M7 6h7v7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    @else
        <div class="mx-auto flex max-w-2xl flex-col items-center justify-center py-10 text-center md:py-16">
            <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full" style="background: #1ed760; color: #09110c; box-shadow: 0 16px 35px rgba(29, 185, 84, 0.24);">
                <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 224 224" fill="currentColor">
                    <path d="M177.7 99c-36-21.4-95.4-23.3-129.7-12.9a10.4 10.4 0 1 1-6.1-19.9c39.4-12 105-9.7 146.4 14.9a10.4 10.4 0 1 1-10.6 17.9Z"/>
                    <path d="M176.5 130.6a8.7 8.7 0 0 1-12 2.9c-30-18.5-75.7-23.8-111.2-13a8.7 8.7 0 1 1-5.1-16.7c40.6-12.3 91-6.3 125.5 14.8a8.7 8.7 0 0 1 2.8 12Z"/>
                    <path d="M162.9 161a7 7 0 0 1-9.6 2.3c-26.2-16-59.2-19.6-98.1-10.7a7 7 0 1 1-3.1-13.6c42.5-9.7 79-5.5 108.5 12.5a7 7 0 0 1 2.3 9.5Z"/>
                </svg>
            </div>
            <div class="font-label text-xs uppercase tracking-wider" style="color: #a7f3c0;">Spotify</div>
            <h2 class="mt-3 font-headline text-2xl font-semibold leading-tight sm:text-3xl">
                {{ ICL_LANGUAGE_CODE == 'ro' ? 'Momentan e liniște.' : 'It is quiet right now.' }}
            </h2>
            <p class="mt-3 max-w-lg text-sm leading-relaxed sm:text-base" style="color: rgba(255, 255, 255, 0.62);">
                {{ ICL_LANGUAGE_CODE == 'ro' ? 'Nu ascult nimic pe Spotify chiar acum.' : 'I am not listening to anything on Spotify at the moment.' }}
            </p>
        </div>
    @endif
</section>
