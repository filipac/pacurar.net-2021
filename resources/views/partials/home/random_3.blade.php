<div class="p-6 h-full flex flex-col items-center justify-center" style="background: var(--color-surface-container-low);">
    <div>
        {{ ICL_LANGUAGE_CODE == 'ro' ? 'Iosua' : 'Joshua' }} says:
    </div>
<div class="w-3/4">
    <audio src="{{get_stylesheet_directory_uri().'/resources/'.(ICL_LANGUAGE_CODE == 'en' ? 'v2_en' : 'v2').'.mp3'}}" controls preload="none" class="w-full"></audio>
</div>
    <div class="mt-2" x-data="{show: false}">
        <div class="text-center">
            <button class="cursor-pointer px-4 py-2 font-label text-xs uppercase tracking-wider transition-colors" @click.prevent="show = !show" style="background: var(--color-surface-container); border-radius: 0.125rem;">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Nu inteleg ce spune' : 'I do not understand' }}</button>
        </div>
        <div class="mt-6 pl-4 italic text-sm" style="border-left: 3px solid var(--color-outline-variant);" :class="{hidden: !show}">
            @if(ICL_LANGUAGE_CODE == 'ro')
            <p>Salutare, bine ati venit pe blogul lui Tata! Sa stiti ca aici nu o sa gasiti desene, v-am avertizat!</p>
            @else
            <p>Hello, welcome to daddy's blog! You should know that you won't find any cartoons around. You've been warned!</p>
            @endif
            <p class="text-right w-full font-bold mt-2">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Iosua' : 'Joshua' }} Pacurar</p>
        </div>
    </div>
</div>
