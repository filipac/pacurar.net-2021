<div class="">{{ ICL_LANGUAGE_CODE == 'ro' ? 'Scris pe' : 'Wrote on' }}
        <div class="inline-block"
        x-data="{geek: false}"
        @mouseover="geek = true;"
        @mouseout="geek = false;"
        @unless(has_post_format('aside'))
            :class='{"bit-rotated": geek}'
         @endunless
        x-text='geek ? "{{ get_the_date('U') }}" : "{{ get_the_date('j F Y'.(is_single() ? ' H:i' : '')) }}"'></div>
    </div>
