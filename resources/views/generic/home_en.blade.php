<div class="flex justify-center text-center pb-8">
    <h1 class="strikethroughLink text-white text-5xl">Hello there 👋!</h1>
</div>
<div class="flex flex-col md:flex-row">
        <div class="bg-white w-full md:w-1/3">
            <div class="p-2 flex">
                <h1 class="strikethroughLink inverse">#aboutMe</h1>
            </div>
            <div @touchstart="$refs.hi.play()" @touchend="$refs.hi.pause();" @mouseover="$refs.hi.play()" @mouseout="$refs.hi.pause();">
                <video poster="{{get_stylesheet_directory_uri().'/resources/hi_poster.jpg'}}" muted x-ref="hi" loop style="object-fit: cover">
                    <source src="{{get_stylesheet_directory_uri().'/resources/hi.mp4'}}" type="video/mp4">
                </video>
            </div>
            <div class="px-2">
                <p>I'm <strong>Filip Pacurar</strong>, welcome to my shiny retro website. I tried to make it as awkward looking as I could.</p>
                <p>The main attraction of this website is <a href="/blog" class="font-bold underline">the blog</a>, but because this is an out of the ordinary website, on the first page you cannot see the latest posts.</p>
            </div>
        </div>
        <div class="bg-yellow w-full md:w-2/3 md:ml-4 mt-4 md:mt-0 flex flex-col p-4">
            <div class="w-full flex justify-center">
                <div class="text-2xl strikethroughLink black">#paidSponsorship <small class="text-xs">(paid by me, of course)</small>
                </div>
            </div>
            <div>
                <p>Hey, <span class="text-2xl font-bold">you</span>! Yes, I'm talking about you! Pay attention for a few minutes here.</p>
                <p class="mt-2">There you go, no that my awesome marketing techniques worked and I have your attention, let's talk serious business.</p>
                <p class="mt-2">I've been a full time programmer for more that 10 years. I've been a geek since general school and highschool and in my free time I exercised web programming, so I gained experience since sort of early in my life.</p>
                <p class="mt-2">
                    Currently I am working full-time with a super Romanian business called <a href="https://www.arnia.com/"
                                                                                              class="strikethroughLink inverse black">Arnia
                        Software</a>.
                    This means that I don't have extra time for other client projects in this period. Nevertheless, here are some of the technologies I work with:
                </p>
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
</p>
                <p class="mt-2">
                    If you are not a technical person, there are little-to-none chances you know some of those above.
                    What I can tell you from my 10+ years experience is that there is no perfect solution nor technology.
                    Each project has it's own requirements so I am never refusing an opportunity to learn new techs.
                </p>
                <p class="mt-2">I work for clients all over the world, Canada, USA and of course, my home country Romania.</p>
                <p class="mt-2">If you want me to make an IT project with you that is not <strong>boring</strong>, we can get in touch on
                    <a href="mailto:filip@pacurar.dev">filip@pacurar.dev</a>, let's talk.
                    Please note that I am not available for new programming projects, all I can offer right now is IT consultancy.</p>
                <p class="mt-4 py-12 text-center w-fulL">
                    <a href="/about-me/it-consultancy" class="btn-cta-consultanta">I want IT consultancy</a>
                </p>
            </div>
        </div>
    </div>
<div class="bg-purple-300 w-full p-3 my-4 shadow-box">
    <h3 class="text-xl"><span class="font-bold strikethroughLink">#writeDaily</span> challenge ✍️</h3>
    <p>I've challenged myself in September 2021 to try and write something daily on this blog, as often as I can.</p>
    <p>The rulse for this challenge are the following:</p>
    <ul class="list-disc list-inside">
        <li>A streak begins when I've published something on a consecutive day</li>
        <li>The streak starts at 2. For example, if I write a post on September 18th, it is currently 0, but if I publish another article the next day
        (Sep. 19th), the streak automatically jumps to 2</li>
        <li>The streak is automatically reset where the last post is not for the current or previous day</li>
        <li>Public or private posts count towards the streak. Private posts are still published on this blog but only visible to me</li>
        <li>It does not matter if the post is in english or romanian</li>
    </ul>
    <p class="mt-2">The source code where I calculate this streak-thing is <a
            href="https://github.com/filipac/pacurar.net-2021/blob/master/app/Jobs/CalculateStreak.php" target="_blank"
            class="strikethroughLink inverse">here</a>
    </p>
    <div class="flex flex-col lg:flex-row w-full border-8 border-yellow mt-4">
        <div
            class="flex-1 flex flex-col items-center justify-center p-10 border-b-8 lg:border-r-8 lg:border-b-0 border-yellow">
            <div class="text-2xl font-bold">Current streak</div>
            <div
                class="text-6xl font-bold mt-4">{{ ($streak = get_option('current_daily_streak_100d')) }} {{ $streak == 0 ? '😢' : '' }} {{ $streak != 0 && $streak < 5 ? '😄' : '' }} {{ $streak != 0 && $streak > 5 ? '💪' : '' }}</div>
        </div>
        <div class="flex-1 flex flex-col items-center justify-center p-10">
            <div class="text-2xl font-bold">My best streak</div>
            <div class="text-6xl font-bold mt-4">{{ get_option('best_daily_streak_100d') }} 🔥</div>
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
                    <div class="flex">
                        <h1 class="strikethroughLink inverse">#easterEggHunt!</h1>
                    </div>
                    <p>Because this is not a boring blog, I've hidden a few interactive bits around here. Are you up to finding all of them? To be fair, I don't even know how many are there, because whenever I get a cool idea, I code it directly without thinking.</p>
                    <p class="mt-2">Let me give you an example hint: on this page you can see me waving my hand to you.</p>
                    <p class="mt-4">Saw it? This is a simple thing, but here are more well hidden things on all those pages.</p>
                    <div class="text-center">
                        <a class="cursor-pointer bg-red-400 p-4 shadow-box hover:shadow-boxhvr inline-block" href="/found-an-easter-egg">I've found an easter-egg!</a>
                    </div>
                </div>
            </div>
            <div class="bg-purple-300 mt-4">
                <div class="p-2 pb-4 text-center">
                    <div class="flex justify-center">
                        <div class="font-bold text-2xl strikethroughLink">#followMe:</div>
                    </div>
                    <div class="font-bold text-xs">(even if you're FBI or something, I got nothing to hide)</div>
                    <div class="flex flex-col md:flex-row items-center justify-center mt-4">
                        <div>
                            <a href="https://twitter.com/filipacro" rel="me" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/tw.png'}}" class="w-16 h-16" alt="Twitter Filip Pacurar">
                            </a>
                        </div>
                        <div class="mt-2 md:mt-0 md:ml-2">
                            <a href="https://github.com/filipac" rel="me" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/github.png'}}" class="w-16 h-16" alt="Twitter Filip Pacurar">
                            </a>
                        </div>
                        <div class="mt-2 md:mt-0 md:ml-2">
                            <a href="https://www.facebook.com/filipacro/" rel="me" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/fb.png'}}" class="w-16 h-16" alt="Facebook Filip Pacurar">
                            </a>
                        </div>
                        <div class="mt-2 md:mt-0 md:ml-2">
                            <a href="https://www.tiktok.com/@filippacurar" rel="me" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/tiktok.png'}}" class="w-16 h-16" alt="TikTok Filip Pacurar">
                            </a>
                        </div>
                        <div class="mt-2 md:mt-0 md:ml-2">
                            <a href="https://www.instagram.com/filipacro/" rel="me" target="_blank">
                                <img src="{{get_stylesheet_directory_uri().'/resources/insta.png'}}" class="w-16 h-16" alt="Instagram Filip Pacurar">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-green-400 mt-4 p-4 flex-1">
                <div class="flex">
                    <div class="font-bold text-4xl strikethroughLink inverse">#copyright...</div>
                </div>
                <div class="mt-2">You know that standard text bit... <div class="italic inline font-bold">copyright &copy; {{ date('Y') }} all rights reserved</div> blah blah..... it does not apply around here 🤣, because even if all the content here belongs to me, I cannot stop you from sharing it, copying the text (even though... why would you copy a text that is about my personal life?) and so on... this is a democratic blog. Do whatever you want. Until I won't let you anymore.</div>
            </div>
        </div>
        <div class="bg-blue-200 w-full md:w-1/3 md:ml-4 mt-4 md:mt-0" x-data="{show: 'dog'}">
            <div class="p-4 text-center">
                <div class="flex justify-center pb-2">
                    <h1 class="strikethroughLink">#meet...</h1>
                </div>
                <template x-if="show == 'dog'">
                    <div>@Naba, our dog</div>
                </template>
                <template x-if="show == 'cat'">
                    <div>@Tom & @Ginger, our cats.</div>
                </template>
                <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block" @click="show = 'cat'" :class="{hidden: show == 'cat'}">Let me see your cats!</div>
                <div class="cursor-pointer bg-green-400 p-1 shadow-box hover:shadow-boxhvr inline-block" @click="show = 'dog'" :class="{hidden: show == 'dog'}">I want the dog again!</div>
            </div>
            <template x-if="show == 'dog'">
                <img src="{{get_stylesheet_directory_uri().'/resources/naba.jpeg'}}" alt="Naba, my doggo."
                     class="w-full">
            </template>
            <template x-if="show == 'cat'">
                <img src="{{get_stylesheet_directory_uri().'/resources/tomginger.jpg'}}" alt="Tom and Ginger..."
                     class="w-full">
            </template>
            <template x-if="show == 'cat'">
                <p class="px-4 py-2 text-sm">
                    We had 5 cats in total. Two left and are wandering somewhere in the neighbourhood, one died :(.
                    If the other 2 come back I will publish a photo of them too. If not... they don't deserve it!</p>
            </template>
        </div>
    </div>
