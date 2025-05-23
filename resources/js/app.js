document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.getElementById('main-navbar-wrapper');
    if (navbar) {
        let lastScrollTop = 0;
        const navbarHeight = navbar.offsetHeight;

        window.addEventListener('scroll', function () {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop && scrollTop > navbarHeight) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, false);
    }

    const fullPageContainer = document.getElementById('fullpage-container');
    if (fullPageContainer) {
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

        const initialHash = window.location.hash.substring(1);
        if (initialHash) {
            const targetSection = sections.findIndex(sec => sec.id === initialHash);
            if (targetSection !== -1) {
                currentSectionIndex = targetSection;
            }
        }
        if (sections.length > 0) {
            sections[currentSectionIndex].scrollIntoView();
            setTimeout(() => { isScrolling = false; }, 100);
        }

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
        window.addEventListener('keydown', function (event) {
            if (isScrolling) return;

            if (event.key === 'ArrowDown' || event.key === 'PageDown' || event.key === 'Enter') {
                event.preventDefault();
                scrollToSection(currentSectionIndex + 1);
            } else if (event.key === 'ArrowUp' || event.key === 'PageUp') {
                event.preventDefault();
                scrollToSection(currentSectionIndex - 1);
            }
        });

        window.addEventListener('touchstart', function (event) {
            if (isScrolling) return;
            touchStartY = event.touches[0].clientY;
        }, { passive: true });

        window.addEventListener('touchend', function (event) {
            if (isScrolling) return;
            const touchEndY = event.changedTouches[0].clientY;
            const swipeDistance = touchStartY - touchEndY;

            if (swipeDistance > scrollThreshold) { 
                scrollToSection(currentSectionIndex + 1);
            } else if (swipeDistance < -scrollThreshold) {
                scrollToSection(currentSectionIndex - 1);
            }
        }, { passive: true });

        const nextButton = document.getElementById('next-section-btn');
        if (nextButton) {
            nextButton.addEventListener('click', () => {
                scrollToSection(currentSectionIndex + 1);
            });
        }
        const prevButton = document.getElementById('prev-section-btn');
        if (prevButton) {
            prevButton.addEventListener('click', () => {
                scrollToSection(currentSectionIndex - 1);
            });
        }

        // Menyesuaikan tinggi section saat window resize (opsional, jika ada elemen dinamis)
        // function adjustSectionHeights() {
        //     sections.forEach(section => {
        //         section.style.height = `${window.innerHeight}px`;
        //     });
        // }
        // window.addEventListener('resize', adjustSectionHeights);
        // adjustSectionHeights(); // Panggil saat load
    }
});
