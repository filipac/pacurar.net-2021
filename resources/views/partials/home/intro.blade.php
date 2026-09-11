@php($isRomanian = ICL_LANGUAGE_CODE == 'ro')
<section class="home-intro grid items-center gap-10 lg:grid-cols-2" aria-labelledby="home-title">
    <div class="home-intro-copy">
        <p class="eyebrow mb-6">
            <span class="hello-dot" aria-hidden="true"></span>
            {{ $isRomanian ? 'Un colț de internet, al meu.' : 'A little corner of the internet, mine.' }}
        </p>
        <h1 id="home-title" class="home-title font-headline font-bold tracking-tight">
            {{ $isRomanian ? 'Salutare' : 'Hello there' }}<span class="hello-period">.</span>
        </h1>
        <p class="home-intro-text mt-6 text-lg md:text-xl leading-relaxed">
            @if($isRomanian)
                Eu sunt <strong>Filip Pacurar</strong>, bine ai venit pe site-ul meu.
                Programator full-stack, consultant IT și scriitor ocazional.
            @else
                I'm <strong>Filip Pacurar</strong>, welcome to my digital corner.
                Full-stack developer, IT consultant, and occasional writer.
            @endif
        </p>
        <div class="flex flex-wrap gap-3 mt-8">
            <a href="/blog" class="journal-button">
                {{ $isRomanian ? 'Citește blog-ul' : 'Read the blog' }}
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            </a>
            <a href="mailto:filip@pacurar.dev" class="journal-button journal-button-secondary">
                {{ $isRomanian ? 'Contactează-mă' : 'Get in touch' }}
                <span class="material-symbols-outlined" aria-hidden="true">mail</span>
            </a>
        </div>
        <p class="home-footnote mt-8 font-label text-xs">
            {{ $isRomanian ? 'Viață, cod și alte întâmplări.' : 'Life, code, and everything in between.' }}
        </p>
    </div>
    <div class="home-photo-wrap">
        <span class="sunburst" aria-hidden="true">✳</span>
        <figure class="home-photo">
            <img src="{{ get_stylesheet_directory_uri().'/resources/fam.jpeg' }}"
                 alt="{{ $isRomanian ? 'Filip împreună cu familia' : 'Filip with his family' }}"
                 width="768" height="504" fetchpriority="high" decoding="async">
            <figcaption class="flex items-center justify-between gap-3">
                <span>{{ $isRomanian ? 'Noi, departe de tastatură.' : 'Us, away from the keyboard.' }}</span>
                <span class="photo-heart" aria-hidden="true">♡</span>
            </figcaption>
        </figure>
    </div>
</section>
