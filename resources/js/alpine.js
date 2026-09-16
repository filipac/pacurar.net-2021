import Alpine from 'alpinejs';
import imageLightbox from './components/image-lightbox';
import healthMasonry from './components/health-masonry';
import healthTrendTooltip from './components/health-trend-tooltip';

window.Alpine = Alpine;
Alpine.data('imageLightbox', imageLightbox);
Alpine.data('healthMasonry', healthMasonry);
Alpine.data('healthTrendTooltip', healthTrendTooltip);
Alpine.start();
