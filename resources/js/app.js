import { initNavbarScroll } from './components/navbarScroll';
import { initFullPageScroll } from './components/fullpageScroll';
import { initServiceGallery } from './components/serviceGallery';
import { initSuccses } from './components/success';

document.addEventListener('DOMContentLoaded', () => {
    initNavbarScroll();
    initFullPageScroll();
    initServiceGallery();
    if (typeof initBookingSlide === 'function') {
        initBookingSlide();
    }
    initSuccses();
});
