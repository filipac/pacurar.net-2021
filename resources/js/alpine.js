import Alpine from 'alpinejs';
import imageLightbox from './components/image-lightbox';
import healthMasonry from './components/health-masonry';

window.Alpine = Alpine;
Alpine.data('imageLightbox', imageLightbox);
Alpine.data('healthMasonry', healthMasonry);
Alpine.start();
