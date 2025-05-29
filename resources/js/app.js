import { initNavbarScroll } from './components/navbarScroll';
import { initFullPageScroll } from './components/fullpageScroll';
import { initServiceGallery } from './components/serviceGallery';

document.addEventListener('DOMContentLoaded', () => {
    initNavbarScroll();
    initFullPageScroll();
    initServiceGallery();
});
