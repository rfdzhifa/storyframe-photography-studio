export function initSuccses() {
    function shareBooking() {
        const bookingCode = '{{ $bookingData["booking_code"] }}';
        const customerName = '{{ $bookingData["customer_name"] }}';
        const bookingDate = '{{ $bookingData["booking_date"] }}';
        const serviceTime = '{{ $bookingData["start_time"] }} - {{ $bookingData["end_time"] }}';

        const text = `🎉 Booking Berhasil!\n\n` +
            `📋 Kode: ${bookingCode}\n` +
            `👤 Nama: ${customerName}\n` +
            `📅 Tanggal: ${bookingDate}\n` +
            `⏰ Waktu: ${serviceTime}\n\n` +
            `Terima kasih telah mempercayai layanan kami! 🙏`;

        if (navigator.share) {
            navigator.share({
                title: 'Detail Booking Studio',
                text: text,
                url: window.location.href
            }).catch(err => {
                console.log('Error sharing:', err);
                fallbackShare(text);
            });
        } else {
            fallbackShare(text);
        }
    }

    function fallbackShare(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showNotification('Detail booking berhasil disalin ke clipboard! 📋');
            }).catch(() => {
                showNotification('Tidak dapat menyalin otomatis. Silakan salin manual.');
            });
        } else {
            // Create temporary textarea for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showNotification('Detail booking berhasil disalin! 📋');
        }
    }

    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
        notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Auto scroll to top when page loads
    window.addEventListener('load', () => {
        window.scrollTo(0, 0);
    });
}