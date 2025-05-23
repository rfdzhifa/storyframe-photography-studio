document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.getElementById('mainNavbar');
    if (!navbar) {
        console.warn('Navbar dengan ID "mainNavbar" tidak ditemukan.');
        return; 
    }

    let lastScrollTop = 0;
    const navbarHeight = navbar.offsetHeight; // Dapatkan tinggi navbar aktual

    window.addEventListener('scroll', function () {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > lastScrollTop) {
            // Scroll ke bawah
            // Sembunyikan navbar jika sudah scroll lebih dari tinggi navbar (atau threshold lain)
            if (scrollTop > navbarHeight) { 
                navbar.style.transform = 'translateY(-100%)';
            }
        } else {
            // Scroll ke atas
            navbar.style.transform = 'translateY(0)';
        }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; 
    }, { passive: true }); // Menambahkan passive: true untuk potensi peningkatan performa scroll
});