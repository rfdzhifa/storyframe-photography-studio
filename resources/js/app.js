import { initNavbarScroll } from './components/navbarScroll';
import { initFullPageScroll } from './components/fullpageScroll';
import { initServiceGallery } from './components/serviceGallery';
import { initBookingSlide } from './components/booking';
import { initSuccses } from './components/success';

document.addEventListener('DOMContentLoaded', () => {
    initNavbarScroll();
    initFullPageScroll();
    initServiceGallery();
    initBookingSlide();
    initSuccses();
});
