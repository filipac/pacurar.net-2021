<div class="bg-white p-6 h-full flex flex-col items-center justify-center">
    <div>
        {{ ICL_LANGUAGE_CODE == 'ro' ? 'Iosua' : 'Joshua' }} says:
    </div>
<div  class="w-3/4">
    <audio src="{{get_stylesheet_directory_uri().'/resources/'.(ICL_LANGUAGE_CODE == 'en' ? 'v2_en' : 'v2').'.mp3'}}" controls class="w-full"></audio>
</div>
    <div class="mt-2" x-data="{show: false}">
        <div class="text-center">
            <div class="cursor-pointer bg-red-400 p-4 shadow-box hover:shadow-boxhvr inline-block" @click.prevent="show = !show">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Nu inteleg ce spune' : 'I do not understand' }}</div>
        </div>
        <div class="border-l-4 border-gray-300 mt-6 pl-4" :class="{hidden: !show}">
            @if(ICL_LANGUAGE_CODE == 'ro')
            <p class="italic">Salutare, bine ati venit pe blogul lui Tata! Sa stiti ca aici nu o sa gasiti desene, v-am avertizat!</p>
            @else
            <p class="italic">Hello, welcome to daddy's blog! You should know that you won't find any cartoons around. You've been warned!</p>
            @endif
            <p class="text-right w-full font-bold">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Iosua' : 'Joshua' }} Pacurar</p>
        </div>
    </div>
</div>
