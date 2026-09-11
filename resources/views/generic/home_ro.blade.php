<div class="home-journal max-w-7xl mx-auto px-4 md:px-8">
    @include('partials.home.intro')

    {{-- About + Skills bento grid --}}
    <section class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-16">
        {{-- About card --}}
        <div class="home-panel home-about md:col-span-5 p-6 md:p-8">
            <h2 class="home-section-title mb-4">Despre Mine</h2>
            <div @touchstart="$refs.hi.play()" @touchend="$refs.hi.pause();" @mouseover="$refs.hi.play()" @mouseout="$refs.hi.pause(); $refs.hi.currentTime = 0;" class="mb-4 overflow-hidden" style="border-radius: 0.25rem;">
                <video poster="{{get_stylesheet_directory_uri().'/resources/hi3.jpg'}}" muted preload="auto" x-ref="hi" loop style="object-fit: cover; width: 100%; max-height: 250px;">
                    <source src="{{get_stylesheet_directory_uri().'/resources/hi3.mp4'}}" type="video/mp4">
                </video>
            </div>
            <p class="text-sm leading-relaxed">Partea principala a site-ului este <a href="/blog" class="font-semibold underline text-primary">blog-ul</a>, dar pentru ca este un site ciudat, pe prima pagina nu vei vedea ultimile postari.</p>
        </div>

        {{-- Skills / Work card --}}
        <div class="home-panel home-work md:col-span-7 p-6 md:p-8">
            <h2 class="home-section-title mb-4">Ce Fac</h2>
            <p class="text-sm leading-relaxed">De vreo 14 ani de zile sunt programator full time. Momentan fac consultanta IT si in timpul liber fac vibe coding cu AI pentru proiecte personale amuzante.</p>
            <div class="flex flex-wrap gap-2 mt-4">
                @foreach(['PHP', 'Laravel', 'React', 'Vue.js', 'React Native', 'Tailwind CSS', 'JavaScript', 'MySQL', 'Swift', 'Java'] as $tech)
                <span class="tech-stamp font-label text-xs px-3 py-2">#{{ strtolower(str_replace([' ', '.'], '', $tech)) }}</span>
                @endforeach
            </div>
            <p class="text-sm leading-relaxed mt-4">Lucrez cu clienti din toata lumea. Ma poti contacta pe <a href="mailto:filip@pacurar.dev" class="font-semibold underline text-primary">filip@pacurar.dev</a>.</p>
            <div class="mt-6">
                <a href="/despre-mine/consultanta-it" class="journal-button">
                    Vreau consultanta IT
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
            <p class="text-sm leading-relaxed mb-4">M-am provocat in Septembrie 2021 sa incerc sa scriu zilnic ceva aici pe blog.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-6 text-center" style="background: var(--color-surface-container); border-radius: 0.25rem;">
                    <div class="font-label text-xs uppercase tracking-wider" style="color: var(--color-on-surface-variant);">Streak-ul curent</div>
                    <div class="font-headline text-5xl font-bold mt-2 text-on-surface">{{ ($streak = get_option('current_daily_streak_100d')) }}</div>
                </div>
                <div class="p-6 text-center" style="background: var(--color-surface-container); border-radius: 0.25rem;">
                    <div class="font-label text-xs uppercase tracking-wider" style="color: var(--color-on-surface-variant);">Cel mai bun streak</div>
                    <div class="font-headline text-5xl font-bold mt-2 text-on-surface">{{ get_option('best_daily_streak_100d') }}</div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA: Blog --}}
    <section class="pb-16">
        <a class="journal-cta block w-full p-6 md:p-8 font-headline text-xl md:text-2xl font-bold" href="/blog">
            Citeste blog-ul &mdash; pentru asta (probabil) ai venit aici!
        </a>
    </section>

    {{-- Bottom grid: Easter egg, social, pets --}}
    <section class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-16">
        {{-- Easter egg hunt --}}
        <div class="home-panel md:col-span-4 p-6">
            <h2 class="home-section-title mb-4">Easter Egg Hunt</h2>
            <p class="text-sm leading-relaxed">Nu este un blog plicticos, am ascuns cateva lucruri interactive. Poti sa le gasesti pe toate?</p>
            <p class="text-sm leading-relaxed mt-2">Indiciu: pe aceasta pagina poti sa ma vezi cum iti fac cu mana.</p>
            <div class="mt-4">
                <a class="inline-flex items-center gap-2 px-4 py-2 font-label text-xs uppercase tracking-wider transition-colors" href="/am-gasit-un-easter-egg" style="background: var(--color-surface-container); color: var(--color-on-surface);">
                    Am gasit unul!
                </a>
            </div>
        </div>

        {{-- Social links --}}
        <div class="home-panel md:col-span-4 p-6">
            <h2 class="home-section-title mb-4">Urmareste-ma</h2>
            <div class="font-label text-xs mb-4" style="color: var(--color-outline);">(chiar daca esti FBI)</div>
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
            <h2 class="home-section-title mb-4">Faceti cunostinta cu...</h2>
            <template x-if="show == 'dog'">
                <div class="text-sm mb-2">@Naba, cainele nostru</div>
            </template>
            <template x-if="show == 'cat'">
                <div class="text-sm mb-2">@Tom & @Ginger, pisicile noastre</div>
            </template>
            <div class="flex gap-2 mb-3">
                <button class="pet-toggle font-label text-xs px-3 py-2 transition-colors" type="button" @click="show = 'cat'" :aria-pressed="show === 'cat'">Pisici</button>
                <button class="pet-toggle font-label text-xs px-3 py-2 transition-colors" type="button" @click="show = 'dog'" :aria-pressed="show === 'dog'">Caine</button>
            </div>
            <template x-if="show == 'dog'">
                <img src="{{get_stylesheet_directory_uri().'/resources/naba.jpeg'}}" alt="Naba" class="w-full" style="border-radius: 0.25rem; object-fit: cover; max-height: 200px;" loading="lazy" decoding="async">
            </template>
            <template x-if="show == 'cat'">
                <div>
                    <img src="{{get_stylesheet_directory_uri().'/resources/tomginger.jpg'}}" alt="Tom si Ginger" class="w-full" style="border-radius: 0.25rem; object-fit: cover; max-height: 200px;" loading="lazy" decoding="async">
                    <p class="text-xs mt-2" style="color: var(--color-on-surface-variant);">Am avut in total 5 pisici. Doua au plecat, una a murit. Daca revin cele 2, pun poza si cu ele.</p>
                </div>
            </template>
        </div>
    </section>
</div>
