<div class="bg-white p-6 h-full flex flex-col items-center justify-center">
    <div>
        {{ ICL_LANGUAGE_CODE == 'ro' ? 'Dedicatie de la mine pentru tine:' : 'A special word from me for you:' }}
    </div>
<div  class="w-3/4">
    <audio src="{{get_stylesheet_directory_uri().'/resources/'.(ICL_LANGUAGE_CODE == 'en' ? 'v1_en' : 'v1').'.mp3'}}" controls class="w-full"></audio>
</div>
</div>
