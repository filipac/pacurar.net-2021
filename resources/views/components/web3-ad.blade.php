@php
    return;
    $spaceName = $spaceName . (! isset($withoutLanguage) ? '-'.ICL_LANGUAGE_CODE : '');
@endphp
<div
    data-web3-space
    data-space-name="{{ $spaceName }}"
    data-language="{{ ICL_LANGUAGE_CODE }}"
    data-format="{{ $format ?? 'light' }}"
    data-sidebar="{{ $sidebar ?? false }}"
    @if(Cache::has('ad-space-info-' . $spaceName))
        data-initial-info='{{ json_encode(
        \Cache::get('ad-space-info-' . $spaceName)
    ) }}'
    @endif
    data-session-id="{{ session()->getId() }}"
>
    @unless(Cache::has('ad-space-info-' . $spaceName) && ($c = Cache::get('ad-space-info-' . $spaceName)) && $c['is_new'])
    {!! \App\Models\AdSpace::getAdSpace($spaceName)->sanitizedHtml() !!}
    @endunless
</div>

@once
    @push('scripts')
        <script src="/tinymce/tinymce.min.js"></script>
        @viteReactRefresh
        @vite(['resources/js/react-app.tsx'])
    @endpush
@endonce
