 <div class="flex flex-col md:flex-row">
        <div class="bg-white w-full md:w-1/3">
            <div class="p-2 text-center">
                <h1>Hello there 👋!</h1>
            </div>
            <div @touchstart="$refs.hi.play()" @touchend="$refs.hi.pause();" @mouseover="$refs.hi.play()" @mouseout="$refs.hi.pause();">
                <video poster="{{get_stylesheet_directory_uri().'/resources/hi_poster.jpg'}}" muted x-ref="hi" loop style="object-fit: cover">
                    <source src="{{get_stylesheet_directory_uri().'/resources/hi.mp4'}}" type="video/mp4">
                </video>
            </div>
            <div class="px-2">
                <p>I'm <strong>Filip Pacurar</strong>, welcome to my shiny retro website. I tried to make it as awkward looking as I could.</p>
                <p>The main attraction of this website is <a href="/blog" class="font-bold underline">the blog</a>, but because this is an out of ordinary website, on the first page you cannot see the latest posts.</p>
            </div>
        </div>
        <div class="bg-yellow w-full md:w-2/3 md:ml-4 mt-4 md:mt-0 flex flex-col p-4">
            <div class="text-2xl w-full text-center">Paid sponsorship <small class="text-xs">(paid by me, of course)</small></div>
            <div>
                <p>Hey, <span class="text-2xl font-bold">you</span>! Yes, I'm talking about you! Pay attention for a few minutes here.</p>
                <p class="mt-2">There you go, no that my awesome marketing techniques worked and I have your attention, let's talk serios business.</p>
                <p class="mt-2">I've been a full time programmer for more that 10 years. I've been a geek since general school and highschool and in my free time I exercised web programming, so I gained experience since sort of early in my life.</p>
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
<span class="text-base">, those are just a few of the techhologies that you have 10% chances of knowing about if you are not in the programming world, but I know them to some extent and I work with those daily.
There is no perfect solution though, that's why we can look for the best solution togheter for your project.
</span>
</p>
<p class="mt-2">I work for clients all over the world, Canada, USA and of course, my home country Romania. So you're all welcomed.</p>
                <p class="mt-2">If you want me to make an IT project with you that is not <strong>boring</strong>, we can get in touch on <a href="mailto:hello@filipac.net">hello@filipac.net</a>, let's talk.</p>
                <p class="mt-4 py-12 text-center w-fulL">
<a href="/about-me/it-consultancy" class="btn-cta-consultanta">I want IT consultancy</a>
</p>
            </div>
        </div>
    </div>
    <a class="block w-full bg-green-200 my-4 p-2 py-4 shadow-box hover:shadow-boxhvr text-center font-bold" href="/blog">
        Read the blog, probabily that's why you are here.
    </a>
    <div class="flex flex-col md:flex-row mb-32">
        <div class="flex flex-col w-full md:w-2/3">
            <div class="bg-pink-200">
                <div class="p-2 pb-4">
                    <h1>Easter-egg hunt!</h1>
                    <p>Because this is not a boring blog, I've hidden a few interactive bits around here. Are you up to finding all of them? To be fair, I don't even know how many are there, because whenever I get a cool idea, I code it directly without thinking.</p>
                    <p class="mt-2">Let me give you an example hint: on this page you can see me waving my hand to you.</p>
                    <p class="mt-4">Ai vazut? Asta e ceva simplu, dar sunt lucruri destul de bine ascunse pe toate paginile.</p>
                    <p class="mt-4">Saw it? This is a simple thing, but here are more well hidden things on all those pages.</p>
                    <div class="text-center">
                        <a class="cursor-pointer bg-red-400 p-4 shadow-box hover:shadow-boxhvr inline-block" href="/found-an-easter-egg">I've found an easter-egg!</a>
                    </div>
                </div>
            </div>
            <div class="bg-purple-300 mt-4">
                <div class="p-2 pb-4 text-center">
                    <div class="font-bold text-2xl">You can follow me on:</div>
                    <div class="font-bold text-xs">(even if you're FBI or something, I got nothing to hide)</div>
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
                <div class="mt-2">You know that standard text bit... <div class="italic inline font-bold">copyright &copy; {{ date('Y') }} all rights reserved</div> blah blah..... it does not apply around here 🤣, because even if all the content here belongs to me, I cannot stop you from sharing it, copying the text (even though... why would you copy a text that is about my personal life?) and so on... this is a democratic blog. Do whatever you want. Until I won't let you anymore.</div>
            </div>
        </div>
        <div class="bg-blue-200 w-full md:w-1/3 md:ml-4 mt-4 md:mt-0" x-data="{show: 'dog'}">
            <div class="p-4 text-center">
                <h1>Please, meet...</h1>
                <template x-if="show == 'dog'">
                    <div>Naba, our dog</div>
                </template>
                <template x-if="show == 'cat'">
                    <div>Pounce - "the cat".</div>
                </template>
                <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block" @click="show = 'cat'" :class="{hidden: show == 'cat'}">Let me see your cat!</div>
                <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block" @click="show = 'dog'" :class="{hidden: show == 'dog'}">I want the dog again!</div>
            </div>
            <template x-if="show == 'dog'">
                    <img src="{{get_stylesheet_directory_uri().'/resources/naba.jpeg'}}" alt="Naba, cainele meu." class="w-full">
                </template>
                <template x-if="show == 'cat'">
                    <img src="{{get_stylesheet_directory_uri().'/resources/pounce.jpeg'}}" alt="Pounce, the \"matza\""." class="w-full">
                </template>
        </div>
    </div>
