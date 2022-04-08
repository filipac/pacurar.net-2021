<div class="flex justify-center text-center pb-8">
    <h1 class="strikethroughLink text-white text-5xl">Salutare 👋!</h1>
</div>
<div class="flex flex-col md:flex-row">
    <div class="bg-white w-full md:w-1/3">
        <div class="p-2 flex">
            <h1 class="strikethroughLink inverse">#despreMine</h1>
        </div>
        <div @touchstart="$refs.hi.play()" @touchend="$refs.hi.pause();" @mouseover="$refs.hi.play()"
             @mouseout="$refs.hi.pause(); $refs.hi.currentTime = 0;">
            <video poster="{{get_stylesheet_directory_uri().'/resources/hi2.jpg'}}" muted preload="auto" x-ref="hi" loop
                   style="object-fit: cover">
                <source src="{{get_stylesheet_directory_uri().'/resources/hi2.mp4'}}" type="video/mp4">
            </video>
        </div>
        <div class="px-2">
            <p>Eu sunt <strong>Filip Pacurar</strong>, bine ai venit pe site-ul meu. Am incercat sa il fac cat de ciudat
                pot eu.</p>
            <p>Partea principala a site-ului este <a href="/blog" class="font-bold underline">blog-ul</a>, dar pentru ca
                este un site ciudat, pe prima pagina nu vei vedea ultimile postari.</p>
        </div>
    </div>
    <div class="bg-yellow w-full md:w-2/3 md:ml-4 mt-4 md:mt-0 flex flex-col p-4">
        <div class="w-full flex justify-center">
            <div class="text-2xl strikethroughLink black">#reclamaSponsorizata <small class="text-xs">de mine...</small>
            </div>
        </div>
        <div>
            <p>Hey, <span class="text-2xl font-bold">tu</span>! Da, despre tine vorbesc! Ia fi atent putin aici.</p>
            <p class="mt-2">Asa, acum ca ti-am captat atentia cu tehnicile mele superbe de marketing, hai sa vorbim
                serios.</p>
            <p class="mt-2">De vreo 10 ani de zile sunt programator full time. Am fost destul de tocilar inca din
                gimnaziu si liceu incat in timpul liber sa exersez programarea web, astfel incat am acumulat ceva
                experienta.</p>
            <p class="mt-2">
                Momentan colaborez full-time cu super-firma din Romania numita <a href="https://www.arnia.com/"
                                                                                  class="strikethroughLink inverse black">Arnia
                    Software</a>.
                Asta inseamna ca nu mai am timp extra de proiecte pentru alti clienti in aceasta perioada. Totusi, uite
                cateva din tehnologiile cu care lucrez eu:
            </p>
            <p class="mt-4">
                <span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#php</span>
                <span
                    class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#laravel</span>
                <span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#html</span>
                <span
                    class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#octobercms</span>
                <span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#react</span>
                <span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#vuejs</span>
                <span
                    class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#reactnative</span>
                <span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#java</span>
                <span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#swift</span>
                <span
                    class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#aureliajs</span>
                <span
                    class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#tailwindcss</span>
                <span
                    class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#bootstrap</span>
                <span
                    class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#javascript</span>
                <span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#mysql</span>

            </p>
            <p class="mt-2">Daca nu esti tehnic, sunt putine sanse sa fi auzit ce multe din tehnologiile de mai sus.
                Totusi, din experienta mea de 10+ ani de zile, nu exista solutia si tehnologia perfecta.
                Fiecare proiect are particularitatile lui, deci nu spun niciodata nu unei tehnologii noi.
            </p>
            <p class="mt-2">Lucrez cu clienti din toata lumea, Canada, USA si desigur, Romania.</p>
            <p class="mt-2">Daca vrei sa facem impreuna un proiect IT care sa nu fie <strong>boring</strong>, ma poti
                contacta pe <a href="mailto:filip@pacurar.dev">filip@pacurar.dev</a> sa intram in legatura. Tine cont de
                faptul ca nu sunt disponibil
                pentru programat decat in cazuri exceptionale, tot ce pot oferi momentan este consultanta IT.</p>
            <p class="mt-4 py-12 text-center w-fulL">
                <a href="/despre-mine/consultanta-it" class="btn-cta-consultanta">Vreau consultanta IT</a>
            </p>
        </div>
    </div>
</div>
<div class="bg-purple-300 w-full p-3 my-4 shadow-box" id="writeDaily">
    <h3 class="text-xl"><span class="font-bold strikethroughLink">#writeDaily</span> challenge ✍️</h3>
    <p>Eu singur m-am provocat in Septembrie 2021 sa incerc sa scriu zilnic ceva aici pe blog, cat de des pot.</p>
    <p>Regulile pentru acest challenge sunt urmatoarele:</p>
    <ul class="list-disc list-inside">
        <li>Un streak incepe in momentul in care am scris in 2 zile consecutive</li>
        <li>Streak-ul incepe de la 2. De exemplu daca scriu o postare pe 18 septembrie este 0, dar daca mai scriu inca
            una in 19 septembrie, automat streak-ul este 2
        </li>
        <li>Streak-ul se reseteaza automat atunci cand ultima postare nu este din ziua curenta sau anterioara</li>
        <li>Se pun la socoteala postarile publice sau private (tot pe blog, dar nu le puteti vedea, sunt doar pentru
            mine)
        </li>
        <li>Nu conteaza daca e postare in engleza sau in romana</li>
    </ul>
    <p class="mt-2">Codul sursa pentru calcularea acestui challenge este <a
            href="https://github.com/filipac/pacurar.net-2021/blob/master/app/Jobs/CalculateStreak.php" target="_blank"
            class="strikethroughLink inverse">aici</a>
    </p>
    <div class="flex flex-col lg:flex-row w-full border-8 border-yellow mt-4">
        <div
            class="flex-1 flex flex-col items-center justify-center p-10 border-b-8 lg:border-r-8 lg:border-b-0 border-yellow">
            <div class="text-2xl font-bold">Streak-ul current</div>
            <div
                class="text-6xl font-bold mt-4">{{ ($streak = get_option('current_daily_streak_100d')) }} {{ $streak == 0 ? '😢' : '' }} {{ $streak != 0 && $streak < 5 ? '😄' : '' }} {{ $streak != 0 && $streak > 5 ? '💪' : '' }}</div>
        </div>
        <div class="flex-1 flex flex-col items-center justify-center p-10">
            <div class="text-2xl font-bold">Cel mai bun streak</div>
            <div class="text-6xl font-bold mt-4">{{ get_option('best_daily_streak_100d') }} 🔥</div>
        </div>
    </div>
</div>
<a class="block w-full bg-green-200 my-4 p-2 py-4 shadow-box hover:shadow-boxhvr text-center font-bold" href="/blog">
    Citeste blog-ul, pentru asta (probabil) ai venit aici!
</a>
<div class="flex flex-col md:flex-row mb-32">
    <div class="flex flex-col w-full md:w-2/3">
        <div class="bg-pink-200">
            <div class="p-2 pb-4">
                <div class="flex">
                    <h1 class="strikethroughLink inverse">#easterEggHunt!</h1>
                </div>
                <p>Pentru ca nu este un blog plicticos, am ascuns cateva lucruri interactive in acest blog. Ce zici,
                    poti sa le gasesti pe toate? Sincer nici eu nu stiu cate sunt, pentru ca le fac pe loc, cum imi vine
                    o idee noua ciudata, cum o implementez.</p>
                <p class="mt-2">Pot sa iti dau un indiciu: pe aceasta pagina poti sa ma vezi cum iti fac cu mana. </p>
                <p class="mt-4">Ai vazut? Asta e ceva simplu, dar sunt lucruri destul de bine ascunse pe toate
                    paginile.</p>
                <div class="text-center">
                    <a class="cursor-pointer bg-red-400 p-4 shadow-box hover:shadow-boxhvr inline-block"
                       href="/am-gasit-un-easter-egg">Am gasit un easter-egg!</a>
                </div>
            </div>
        </div>
        <div class="bg-purple-300 mt-4">
            <div class="p-2 pb-4 text-center">
                <div class="flex justify-center">
                    <div class="font-bold text-2xl strikethroughLink">#urmaresteMa:</div>
                </div>
                <div class="font-bold text-xs">(chiar daca esti FBI sau ceva, nu am nimic de ascuns)</div>
                <div class="flex flex-col md:flex-row items-center justify-center mt-4">
                    <div>
                        <a href="https://twitter.com/filipacro" rel="me" target="_blank">
                            <img src="{{get_stylesheet_directory_uri().'/resources/tw.png'}}" class="w-16 h-16"
                                 alt="Twitter Filip Pacurar">
                        </a>
                    </div>
                    <div class="mt-2 md:mt-0 md:ml-2">
                        <a href="https://github.com/filipac" rel="me" target="_blank">
                            <img src="{{get_stylesheet_directory_uri().'/resources/github.png'}}" class="w-16 h-16"
                                 alt="Twitter Filip Pacurar">
                        </a>
                    </div>
                    <div class="mt-2 md:mt-0 md:ml-2">
                        <a href="https://www.facebook.com/filipacro/" rel="me" target="_blank">
                            <img src="{{get_stylesheet_directory_uri().'/resources/fb.png'}}" class="w-16 h-16"
                                 alt="Facebook Filip Pacurar">
                        </a>
                    </div>
                    <div class="mt-2 md:mt-0 md:ml-2">
                        <a href="https://www.tiktok.com/@filippacurar" rel="me" target="_blank">
                            <img src="{{get_stylesheet_directory_uri().'/resources/tiktok.png'}}" class="w-16 h-16"
                                 alt="TikTok Filip Pacurar">
                        </a>
                    </div>
                    <div class="mt-2 md:mt-0 md:ml-2">
                        <a href="https://www.instagram.com/filipacro/" rel="me" target="_blank">
                            <img src="{{get_stylesheet_directory_uri().'/resources/insta.png'}}" class="w-16 h-16"
                                 alt="Instagram Filip Pacurar">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-green-400 mt-4 p-4 flex-1">
            <div class="flex">
                <div class="font-bold text-4xl strikethroughLink inverse">#copyright...</div>
            </div>
            <div class="mt-2">Stiti voi tot textul ăla standard...
                <div class="italic inline font-bold">copyright &copy; {{ date('Y') }} toate drepturile rezervate</div>
                bla bla..... aici nu prea se aplica :)), pentru ca desi tot continutul imi apartine, nu va pot opri sa
                dati share, copy la text (de ce ati copia un text in care va zic ceva personal) si asa mai departe, este
                un blog democratic. Faceti ce vreti. Pana ma enervez eu si nu va mai dau voie.
            </div>
        </div>
    </div>
    <div class="bg-blue-200 w-full md:w-1/3 md:ml-4 mt-4 md:mt-0" x-data="{show: 'dog'}">
        <div class="p-4 text-center">
            <div class="flex justify-center pb-2">
                <h1 class="strikethroughLink">#facetiCunostintaCu...</h1>
            </div>
            <template x-if="show == 'dog'">
                <div>@Naba, cainele nostru.</div>
            </template>
            <template x-if="show == 'cat'">
                <div>@Tom & @Ginger, pisicile noastre.</div>
            </template>
            <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block"
                 @click="show = 'cat'" :class="{hidden: show == 'cat'}">Vreau sa vad pisicile!
            </div>
            <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block"
                 @click="show = 'dog'" :class="{hidden: show == 'dog'}">Vreau sa vad iar cainele!
            </div>
        </div>
        <template x-if="show == 'dog'">
            <img src="{{get_stylesheet_directory_uri().'/resources/naba.jpeg'}}" alt="Naba, cainele meu."
                 class="w-full">
        </template>
        <template x-if="show == 'cat'">
            <img src="{{get_stylesheet_directory_uri().'/resources/tomginger.jpg'}}" alt="Tom and Ginger..."
                 class="w-full">
        </template>
        <template x-if="show == 'cat'">
            <p class="px-4 py-2 text-sm">Am avut in total 5 pisici. Doua au plecat si sunt undeva prin cartier, una a
                murit din pacate. Daca revin cele 2, pun poza si cu ele. Daca nu... nu merita!</p>
        </template>
    </div>
</div>
