// fullpageScroll.js
export function initFullPageScroll() {
    const fullPageContainer = document.getElementById('fullpage-container');
    if (!fullPageContainer) return;

    const sections = Array.from(fullPageContainer.getElementsByClassName('full-page-section'));
    let currentSectionIndex = 0;
    let isScrolling = false;
    const scrollThreshold = 50;
    let touchStartY = 0;

    function scrollToSection(index) {
        if (index >= 0 && index < sections.length && !isScrolling) {
            isScrolling = true;
            currentSectionIndex = index;
            sections[index].scrollIntoView({ behavior: 'smooth' });

            setTimeout(() => {
                isScrolling = false;
            }, 1000);
        }
    }

    // Handle hash navigation on load
    const initialHash = window.location.hash.substring(1);
    if (initialHash) {
        const targetIndex = sections.findIndex(sec => sec.id === initialHash);
        if (targetIndex !== -1) currentSectionIndex = targetIndex;
    }

    if (sections.length > 0) {
        sections[currentSectionIndex].scrollIntoView();
        setTimeout(() => { isScrolling = false; }, 100);
    }

    // Scroll via mouse wheel
    let wheelTimeout;
    window.addEventListener('wheel', function (event) {
        if (isScrolling) return;
        clearTimeout(wheelTimeout);
        wheelTimeout = setTimeout(() => {
            if (event.deltaY > scrollThreshold) {
                scrollToSection(currentSectionIndex + 1);
            } else if (event.deltaY < -scrollThreshold) {
                scrollToSection(currentSectionIndex - 1);
            }
        }, 150);
    }, { passive: false });

    // Scroll via keyboard
    window.addEventListener('keydown', function (event) {
        if (isScrolling) return;
        if (['ArrowDown', 'PageDown', 'Enter'].includes(event.key)) {
            event.preventDefault();
            scrollToSection(currentSectionIndex + 1);
        } else if (['ArrowUp', 'PageUp'].includes(event.key)) {
            event.preventDefault();
            scrollToSection(currentSectionIndex - 1);
        }
    });

    // Scroll via touch swipe
    window.addEventListener('touchstart', e => {
        if (isScrolling) return;
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    window.addEventListener('touchend', e => {
        if (isScrolling) return;
        const swipe = touchStartY - e.changedTouches[0].clientY;
        if (swipe > scrollThreshold) {
            scrollToSection(currentSectionIndex + 1);
        } else if (swipe < -scrollThreshold) {
            scrollToSection(currentSectionIndex - 1);
        }
    }, { passive: true });

    // Button click
    const nextButton = document.getElementById('next-section-btn');
    const prevButton = document.getElementById('prev-section-btn');

    if (nextButton) nextButton.addEventListener('click', () => scrollToSection(currentSectionIndex + 1));
    if (prevButton) prevButton.addEventListener('click', () => scrollToSection(currentSectionIndex - 1));

    // Resize handling
    function adjustSectionHeights() {
        sections.forEach(section => {
            section.style.height = `${window.innerHeight}px`;
        });
    }

    window.addEventListener('resize', adjustSectionHeights);
    adjustSectionHeights();
}
