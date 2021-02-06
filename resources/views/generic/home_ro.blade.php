 <div class="flex flex-col md:flex-row">
        <div class="bg-white w-full md:w-1/3">
            <div class="p-2 text-center">
                <h1>Salutare 👋!</h1>
            </div>
            <div @touchstart="$refs.hi.play()" @touchend="$refs.hi.pause();" @mouseover="$refs.hi.play()" @mouseout="$refs.hi.pause();">
                <video poster="{{get_stylesheet_directory_uri().'/resources/hi_poster.jpg'}}" muted x-ref="hi" loop style="object-fit: cover">
                    <source src="{{get_stylesheet_directory_uri().'/resources/hi.mp4'}}" type="video/mp4">
                </video>
            </div>
            <div class="px-2">
                <p>Eu sunt <strong>Filip Pacurar</strong>, bine ai venit pe site-ul meu. Am incercat sa il fac cat de ciudat pot eu.</p>
                <p>Partea principala a site-ului este <a href="/blog" class="font-bold underline">blog-ul</a>, dar pentru ca este un site ciudat, pe prima pagina nu vei vedea ultimile postari.</p>
            </div>
        </div>
        <div class="bg-yellow w-full md:w-2/3 md:ml-4 mt-4 md:mt-0 flex flex-col p-4">
            <div class="text-2xl w-full text-center">Reclama sponsorizata <small class="text-xs">de mine...</small></div>
            <div>
                <p>Hey, <span class="text-2xl font-bold">tu</span>! Da, despre tine vorbesc! Ia fi atent putin aici.</p>
                <p class="mt-2">Asa, acum ca ti-am captat atentia cu tehnicile mele superbe de marketing, hai sa vorbim serios.</p>
                <p class="mt-2">De vreo 10 ani de zile sunt programator full time. Am fost destul de tocilar inca din gimnaziu si liceu incat in timpul liber sa exersez programarea web, astfel incat am acumulat ceva experienta.</p>
                <p class="mt-4">
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#php</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#laravel</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#html</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#octobercms</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#react</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#vuejs</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#reactnative</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#java</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#swift</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#aureliajs</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#tailwindcss</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#bootstrap</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#javascript</span>
<span class="text-grey-200 text-xs hover:bg-red-400 hover:text-white hover:underline ml-1">#mysql</span>
<span class="text-base">sunt doar cateva din tehnologiile pe care daca nu esti tehnic sunt 10% sanse sa le fi auzit, dar eu le stapanesc si lucrez zilnic cu ele.
Nu exista totusi solutia pefecta, de aceea in orele de consultanta vom cauta impreuna cea mai buna solutie pentru
proiectul tau.
</span>
</p>
<p class="mt-2">Lucrez cu clienti din toata lumea, Canada, USA si desigur, Romania. Asa ca toti sunteti bineveniti.</p>
                <p class="mt-2">Daca vrei sa facem impreuna un proiect IT care sa nu fie <strong>boring</strong>, ma poti contacta pe <a href="mailto:hello@filipac.net">hello@filipac.net</a> sa intram in legatura.</p>
                <p class="mt-4 py-12 text-center w-fulL">
<a href="/despre-mine/consultanta-it" class="btn-cta-consultanta">Vreau consultanta IT</a>
</p>
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
                    <h1>Easter-egg hunt!</h1>
                    <p>Pentru ca nu este un blog plicticos, am ascuns cateva lucruri interactive in acest blog. Ce zici, poti sa le gasesti pe toate? Sincer nici eu nu stiu cate sunt, pentru ca le fac pe loc, cum imi vine o idee noua ciudata, cum o implementez.</p>
                    <p class="mt-2">Pot sa iti dau un indiciu: pe aceasta pagina poti sa ma vezi cum iti fac cu mana. </p>
                    <p class="mt-4">Ai vazut? Asta e ceva simplu, dar sunt lucruri destul de bine ascunse pe toate paginile.</p>
                    <div class="text-center">
                        <a class="cursor-pointer bg-red-400 p-4 shadow-box hover:shadow-boxhvr inline-block" href="/am-gasit-un-easter-egg">Am gasit un easter-egg!</a>
                    </div>
                </div>
            </div>
            <div class="bg-purple-300 mt-4">
                <div class="p-2 pb-4 text-center">
                    <div class="font-bold text-2xl">Ma poti urmari pe:</div>
                    <div class="font-bold text-xs">(chiar daca esti FBI sau ceva, nu am nimic de ascuns)</div>
                    <div class="flex flex-col md:flex-row items-center justify-center mt-4">
                        <div>
                            <a href="https://twitter.com/filipacro" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/tw.png'}}" class="w-16 h-16" alt="Twitter Filip Pacurar">
                            </a>
                        </div>
                        <div class="mt-2 md:mt-0 md:ml-2">
                            <a href="https://www.facebook.com/filipacro/" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/fb.png'}}" class="w-16 h-16" alt="Facebook Filip Pacurar">
                            </a>
                        </div>
                        <div class="mt-2 md:mt-0 md:ml-2">
                            <a href="https://www.tiktok.com/@filippacurar" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/tiktok.png'}}" class="w-16 h-16" alt="TikTok Filip Pacurar">
                            </a>
                        </div>
                        <div class="mt-2 md:mt-0 md:ml-2">
                            <a href="https://www.instagram.com/filipacro/" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/insta.png'}}" class="w-16 h-16" alt="Instagram Filip Pacurar">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-green-400 mt-4 p-4 flex-1">
                <div class="font-bold text-4xl">Copyright...</div>
                <div class="mt-2">Stiti voi tot textul ăla standard... <div class="italic inline font-bold">copyright &copy; {{ date('Y') }} toate drepturile rezervate</div> bla bla..... aici nu prea se aplica :)), pentru ca desi tot continutul imi apartine, nu va pot opri sa dati share, copy la text (de ce ati copia un text in care va zic ceva personal) si asa mai departe, este un blog democratic. Faceti ce vreti. Pana ma enervez eu si nu va mai dau voie.</div>
            </div>
        </div>
        <div class="bg-blue-200 w-full md:w-1/3 md:ml-4 mt-4 md:mt-0" x-data="{show: 'dog'}">
            <div class="p-4 text-center">
                <h1>Faceti cunostinta cu...</h1>
                <template x-if="show == 'dog'">
                    <div>Naba, cainele nostru.</div>
                </template>
                <template x-if="show == 'cat'">
                    <div>Pounce - "the cat".</div>
                </template>
                <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block" @click="show = 'cat'" :class="{hidden: show == 'cat'}">Vreau sa vad pisica!</div>
                <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block" @click="show = 'dog'" :class="{hidden: show == 'dog'}">Vreau sa vad iar cainele!</div>
            </div>
            <template x-if="show == 'dog'">
                    <img src="{{get_stylesheet_directory_uri().'/resources/naba.jpeg'}}" alt="Naba, cainele meu." class="w-full">
                </template>
                <template x-if="show == 'cat'">
                    <img src="{{get_stylesheet_directory_uri().'/resources/pounce.jpeg'}}" alt="Pounce, the \"matza\""." class="w-full">
                </template>
        </div>
    </dizv>
