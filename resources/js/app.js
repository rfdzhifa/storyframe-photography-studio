// File: resources/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. Animasi Navbar Sembunyi/Muncul ---
    const navbar = document.getElementById('main-navbar-wrapper'); // Kita akan tambahkan ID ini di navbar.blade.php
    if (navbar) {
        let lastScrollTop = 0;
        const navbarHeight = navbar.offsetHeight;

        window.addEventListener('scroll', function () {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop && scrollTop > navbarHeight) {
                // Scroll ke bawah dan sudah melewati tinggi navbar
                navbar.style.transform = 'translateY(-100%)';
            } else {
                // Scroll ke atas atau masih di area atas
                navbar.style.transform = 'translateY(0)';
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; // Untuk handle scroll ke paling atas
        }, false);
    }

    // --- 2. Navigasi Section Full-Screen ---
    const fullPageContainer = document.getElementById('fullpage-container');
    if (fullPageContainer) {
        const sections = Array.from(fullPageContainer.getElementsByClassName('full-page-section'));
        let currentSectionIndex = 0;
        let isScrolling = false; // Flag untuk mencegah multiple scroll triggers
        const scrollThreshold = 50; // Jumlah scroll (px) untuk trigger pindah section
        let touchStartY = 0;

        // Fungsi untuk pindah ke section tertentu
        function scrollToSection(index) {
            if (index >= 0 && index < sections.length && !isScrolling) {
                isScrolling = true;
                currentSectionIndex = index;
                sections[index].scrollIntoView({ behavior: 'smooth' });

                // Update URL hash (opsional, bagus untuk deep linking & navigasi browser)
                // window.location.hash = sections[index].id || `section-${index}`;

                // Reset flag setelah animasi scroll selesai (kurang lebih)
                setTimeout(() => {
                    isScrolling = false;
                }, 1000); // Sesuaikan durasi dengan kecepatan 'smooth' scroll
            }
        }

        // Inisialisasi: scroll ke section pertama atau section dari hash URL
        const initialHash = window.location.hash.substring(1);
        if (initialHash) {
            const targetSection = sections.findIndex(sec => sec.id === initialHash);
            if (targetSection !== -1) {
                currentSectionIndex = targetSection;
            }
        }
        // Langsung scroll ke section awal tanpa animasi agar tidak aneh saat load
        if (sections.length > 0) {
            sections[currentSectionIndex].scrollIntoView();
            // Set timeout kecil untuk memastikan posisi awal sudah benar sebelum event listener aktif
            setTimeout(() => { isScrolling = false; }, 100);
        }


        // Event listener untuk mouse wheel
        let wheelTimeout;
        window.addEventListener('wheel', function (event) {
            if (isScrolling) return;
            // event.preventDefault(); // Hati-hati dengan ini, bisa mengganggu scroll normal di elemen lain

            clearTimeout(wheelTimeout);
            wheelTimeout = setTimeout(() => {
                if (event.deltaY > scrollThreshold) {
                    // Scroll ke bawah
                    scrollToSection(currentSectionIndex + 1);
                } else if (event.deltaY < -scrollThreshold) {
                    // Scroll ke atas
                    scrollToSection(currentSectionIndex - 1);
                }
            }, 150); // Debounce untuk wheel event
        }, { passive: false }); // passive:false diperlukan jika mau preventDefault

        // Event listener untuk keyboard (Arrow Up, Arrow Down, Enter, PageDown, PageUp)
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

        // Event listener untuk touch events (swipe up/down)
        window.addEventListener('touchstart', function (event) {
            if (isScrolling) return;
            touchStartY = event.touches[0].clientY;
        }, { passive: true });

        window.addEventListener('touchend', function (event) {
            if (isScrolling) return;
            const touchEndY = event.changedTouches[0].clientY;
            const swipeDistance = touchStartY - touchEndY;

            if (swipeDistance > scrollThreshold) { // Swipe ke atas (konten bergerak ke atas, user swipe jari ke atas)
                scrollToSection(currentSectionIndex + 1);
            } else if (swipeDistance < -scrollThreshold) { // Swipe ke bawah
                scrollToSection(currentSectionIndex - 1);
            }
        }, { passive: true });


        // Setup untuk tombol navigasi (jika ada)
        // Contoh: <button id="next-section-btn">Next</button>
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
