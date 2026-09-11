import Alpine from 'alpinejs';
import imageLightbox from './components/image-lightbox';

window.Alpine = Alpine;
Alpine.data('imageLightbox', imageLightbox);
Alpine.start();
