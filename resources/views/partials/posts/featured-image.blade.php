<div class="reading-cover" @if($isPortraitThumbnail) x-data="imageLightbox" @endif>
    @if($isPortraitThumbnail)
        <button type="button" class="reading-cover-link" @click="open()" aria-haspopup="dialog"
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
        </button>

        <dialog class="image-lightbox" x-ref="dialog"
                aria-label="{{ $isRomanian ? 'Imaginea articolului' : 'Article image' }}"
                @click="if ($event.target === $refs.dialog) close()"
                @keydown.escape.stop @close="onClose()">
            <button type="button" class="image-lightbox-close" @click="close()" autofocus
                    aria-label="{{ $isRomanian ? 'Închide imaginea' : 'Close image' }}">
                <span aria-hidden="true">×</span>
            </button>
            <template x-if="loaded">
                <img class="image-lightbox-image" src="{{ $thumbnailDimensions[0] }}"
                     width="{{ $thumbnailDimensions[1] }}" height="{{ $thumbnailDimensions[2] }}"
                     alt="{{ get_post_meta($thumbnailId, '_wp_attachment_image_alt', true) }}"
                     decoding="async">
            </template>
        </dialog>
    @endif
</div>
