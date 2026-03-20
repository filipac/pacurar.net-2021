<x-layouts.master>
    <x-slot name="belowContent">
        @php
            $lang = ICL_LANGUAGE_CODE;
        @endphp

        {{-- Language Switcher --}}
        <div class="flex items-center justify-center py-4">
            @php
                do_action( 'wpml_add_language_selector' )
            @endphp
        </div>
        <style>
            .switcher .wpml-ls-statics-shortcode_actions {
                background-color: var(--color-surface-container);
                margin-bottom: 20px;
                border-radius: 0.25rem;
            }
        </style>

        @includeWhen($lang == 'ro', 'generic.home_ro')
        @includeWhen($lang != 'ro', 'generic.home_en')
    </x-slot>
</x-layouts.master>
