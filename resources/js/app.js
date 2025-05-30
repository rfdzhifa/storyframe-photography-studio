import { initNavbarScroll } from './components/navbarScroll';
import { initFullPageScroll } from './components/fullpageScroll';
import { initServiceGallery } from './components/serviceGallery';
import { initBookingSlide } from './components/booking';

document.addEventListener('DOMContentLoaded', () => {
    initNavbarScroll();
    initFullPageScroll();
    initServiceGallery();
    initBookingSlide();
});
