@php
    $search = get_query_var('s');
@endphp
<form method="GET" action="/">
    <div class="w-full flex flex-col justify-center items-center pt-24 pb-12 px-4 md:px-0">
        {{-- Filipoogle logo --}}
        <div class="mb-8" style="max-width: 300px;">
            @include('partials.logo_search')
        </div>

        {{-- Search input --}}
        <div class="relative w-full md:w-2/3 mx-auto">
            <input class="w-full h-12 px-5 pr-12 text-sm focus:outline-none"
                   type="search" name="s" value="{{ $search }}"
                   placeholder="{{ ICL_LANGUAGE_CODE == 'ro' ? 'Cauta aici' : 'Search here' }}.."
                   style="background: var(--color-surface-container-low); border: 1px solid var(--color-outline-variant); border-radius: 0.25rem; color: var(--color-on-surface);">
            <button type="submit" class="absolute right-0 top-0 h-12 px-4">
                <span class="material-symbols-outlined" style="font-size: 20px; color: var(--color-on-surface-variant);">search</span>
            </button>
        </div>

        {{-- Buttons --}}
        @unless(isset($search))
        <div class="flex flex-col md:flex-row mt-4 gap-2">
            <input type="submit" name="submit" value="Filipoogle Search"
                   class="px-6 py-2 font-label text-xs uppercase tracking-wider cursor-pointer transition-colors"
                   style="background: var(--color-surface-container); color: var(--color-on-surface); border-radius: 0.125rem;">
            <input type="submit" name="submit" value="{{ ICL_LANGUAGE_CODE == 'ro' ? 'Ma simt baftos' : 'I feel lucky' }}"
                   class="px-6 py-2 font-label text-xs uppercase tracking-wider cursor-pointer transition-colors"
                   style="background: var(--color-surface-container); color: var(--color-on-surface); border-radius: 0.125rem;">
        </div>
        @endif
    </div>
</form>
