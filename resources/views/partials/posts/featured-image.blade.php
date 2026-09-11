<div class="reading-cover">
    @if($isPortraitThumbnail)
        <a class="reading-cover-link" href="{{ $thumbnailDimensions[0] }}"
           aria-label="{{ $isRomanian ? 'Vezi imaginea la dimensiune completă' : 'View the full-size image' }}">
    @endif

    {!! wp_get_attachment_image(
        $thumbnailId,
        'full',
        false,
        [
            'class' => 'reading-cover-image',
            'fetchpriority' => 'high',
            'loading' => 'eager',
            'decoding' => 'async',
            'sizes' => $isPortraitThumbnail
                ? '(min-width: 1024px) 288px, 240px'
                : '(min-width: 1280px) 1120px, (min-width: 768px) calc(100vw - 4rem), calc(100vw - 2rem)',
        ]
    ) !!}

    @if($isPortraitThumbnail)
            <span class="reading-cover-expand" aria-hidden="true">↗</span>
        </a>
    @endif
</div>
