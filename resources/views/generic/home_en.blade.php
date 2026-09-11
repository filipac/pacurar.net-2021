<div class="home-journal max-w-7xl mx-auto px-4 md:px-8">
    @include('partials.home.intro')

    {{-- About + Skills bento grid --}}
    <section class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-16">
        {{-- About card --}}
        <div class="home-panel home-about md:col-span-5 p-6 md:p-8">
            <h2 class="home-section-title mb-4">About Me</h2>
            <div @touchstart="$refs.hi.play()" @touchend="$refs.hi.pause();" @mouseover="$refs.hi.play()" @mouseout="$refs.hi.pause(); $refs.hi.currentTime = 0;" class="mb-4 overflow-hidden" style="border-radius: 0.25rem;">
                <video poster="{{get_stylesheet_directory_uri().'/resources/hi3.jpg'}}" muted preload="auto" x-ref="hi" loop style="object-fit: cover; width: 100%; max-height: 250px;">
                    <source src="{{get_stylesheet_directory_uri().'/resources/hi3.mp4'}}" type="video/mp4">
                </video>
            </div>
            <p class="text-sm leading-relaxed">The main attraction of this website is <a href="/blog" class="font-semibold underline text-primary">the blog</a>, but because this is an out of the ordinary website, on the first page you cannot see the latest posts.</p>
        </div>

        {{-- Skills / Work card --}}
        <div class="home-panel home-work md:col-span-7 p-6 md:p-8">
            <h2 class="home-section-title mb-4">What I Do</h2>
            <p class="text-sm leading-relaxed">I've been a full time programmer for more than 14 years. Currently I am doing IT consultancy and in my free time I play with AI vibe coding for personal fun projects.</p>
            <div class="flex flex-wrap gap-2 mt-4">
                @foreach(['PHP', 'Laravel', 'React', 'Vue.js', 'React Native', 'Tailwind CSS', 'JavaScript', 'MySQL', 'Swift', 'Java'] as $tech)
                <span class="tech-stamp font-label text-xs px-3 py-2">#{{ strtolower(str_replace([' ', '.'], '', $tech)) }}</span>
                @endforeach
            </div>
            <p class="text-sm leading-relaxed mt-4">I work for clients all over the world. If you want me to make an IT project with you, we can get in touch on <a href="mailto:filip@pacurar.dev" class="font-semibold underline text-primary">filip@pacurar.dev</a>.</p>
            <div class="mt-6">
                <a href="/about-me/it-consultancy" class="journal-button">
                    I want IT consultancy
                </a>
            </div>
        </div>
    </section>

    {{-- System Status: writeDaily streak --}}
    <section class="pb-16" id="writeDaily">
        <div class="home-panel home-streak p-6 md:p-8">
            <h2 class="font-label text-xs uppercase tracking-wider mb-2" style="color: var(--color-on-surface-variant);">
                <span class="material-symbols-outlined align-middle" style="font-size: 14px;">terminal</span>
                System Status // #writeDaily
            </h2>
            <p class="text-sm leading-relaxed mb-4">I've challenged myself in September 2021 to try and write something daily on this blog, as often as I can.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-6 text-center" style="background: var(--color-surface-container); border-radius: 0.25rem;">
                    <div class="font-label text-xs uppercase tracking-wider" style="color: var(--color-on-surface-variant);">Current streak</div>
                    <div class="font-headline text-5xl font-bold mt-2 text-on-surface">{{ ($streak = get_option('current_daily_streak_100d')) }}</div>
                </div>
                <div class="p-6 text-center" style="background: var(--color-surface-container); border-radius: 0.25rem;">
                    <div class="font-label text-xs uppercase tracking-wider" style="color: var(--color-on-surface-variant);">Best streak</div>
                    <div class="font-headline text-5xl font-bold mt-2 text-on-surface">{{ get_option('best_daily_streak_100d') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA: Blog --}}
    <section class="pb-16">
        <a class="journal-cta block w-full p-6 md:p-8 font-headline text-xl md:text-2xl font-bold" href="/blog">
            Read the blog &mdash; probably that's why you are here.
        </a>
    </section>

    {{-- Bottom grid: Easter egg, social, pets --}}
    <section class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-16">
        {{-- Easter egg hunt --}}
        <div class="home-panel md:col-span-4 p-6">
            <h2 class="home-section-title mb-4">Easter Egg Hunt</h2>
            <p class="text-sm leading-relaxed">Because this is not a boring blog, I've hidden a few interactive bits around here. Are you up to finding all of them?</p>
            <p class="text-sm leading-relaxed mt-2">Hint: on this page you can see me waving my hand to you.</p>
            <div class="mt-4">
                <a class="inline-flex items-center gap-2 px-4 py-2 font-label text-xs uppercase tracking-wider transition-colors" href="/found-an-easter-egg" style="background: var(--color-surface-container); color: var(--color-on-surface);">
                    I found one!
                </a>
            </div>
        </div>

        {{-- Social links --}}
        <div class="home-panel md:col-span-4 p-6">
            <h2 class="home-section-title mb-4">Follow Me</h2>
            <div class="font-label text-xs mb-4" style="color: var(--color-outline);">(even if you're FBI or something)</div>
            <div class="flex flex-col gap-2">
                <a href="https://twitter.com/filipacro" rel="me" target="_blank" class="flex items-center gap-2 text-sm hover:text-primary transition-colors">
                    <span class="material-symbols-outlined" style="font-size: 18px;">open_in_new</span> Twitter
                </a>
                <a href="https://github.com/filipac" rel="me" target="_blank" class="flex items-center gap-2 text-sm hover:text-primary transition-colors">
                    <span class="material-symbols-outlined" style="font-size: 18px;">code</span> GitHub
                </a>
                <a href="https://www.instagram.com/filipacro/" rel="me" target="_blank" class="flex items-center gap-2 text-sm hover:text-primary transition-colors">
                    <span class="material-symbols-outlined" style="font-size: 18px;">photo_camera</span> Instagram
                </a>
                <a href="https://www.tiktok.com/@filippacurar" rel="me" target="_blank" class="flex items-center gap-2 text-sm hover:text-primary transition-colors">
                    <span class="material-symbols-outlined" style="font-size: 18px;">play_circle</span> TikTok
                </a>
            </div>
        </div>

        {{-- Pets --}}
        <div class="home-panel md:col-span-4 p-6" x-data="{show: 'dog'}">
            <h2 class="home-section-title mb-4">Meet...</h2>
            <template x-if="show == 'dog'">
                <div class="text-sm mb-2">@Naba, our dog</div>
            </template>
            <template x-if="show == 'cat'">
                <div class="text-sm mb-2">@Tom & @Ginger, our cats</div>
            </template>
            <div class="flex gap-2 mb-3">
                <button class="pet-toggle font-label text-xs px-3 py-2 transition-colors" type="button" @click="show = 'cat'" :aria-pressed="show === 'cat'">Cats</button>
                <button class="pet-toggle font-label text-xs px-3 py-2 transition-colors" type="button" @click="show = 'dog'" :aria-pressed="show === 'dog'">Dog</button>
            </div>
            <template x-if="show == 'dog'">
                <img src="{{get_stylesheet_directory_uri().'/resources/naba.jpeg'}}" alt="Naba" class="w-full" style="border-radius: 0.25rem; object-fit: cover; max-height: 200px;" loading="lazy" decoding="async">
            </template>
            <template x-if="show == 'cat'">
                <div>
                    <img src="{{get_stylesheet_directory_uri().'/resources/tomginger.jpg'}}" alt="Tom and Ginger" class="w-full" style="border-radius: 0.25rem; object-fit: cover; max-height: 200px;" loading="lazy" decoding="async">
                    <p class="text-xs mt-2" style="color: var(--color-on-surface-variant);">We had 5 cats in total. Two left, one died. If the other 2 come back I will publish a photo of them too.</p>
                </div>
            </template>
        </div>
    </section>
</div>
