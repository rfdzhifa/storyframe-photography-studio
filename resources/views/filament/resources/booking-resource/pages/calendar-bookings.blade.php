<x-filament-panels::page>
    <div
        id="booking-calendar"
        wire:ignore
        style="min-height: 650px;"
    ></div>

    {{-- FullCalendar CSS --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css"
    >

    {{-- PENTING: pakai index.global.min.js, bukan main.min.js --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('booking-calendar');
            if (!el) return;

            const events = @json($events);
            console.log('booking events', events);

            // Sekarang FullCalendar sudah tersedia sebagai global
            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay',
                },
                events: events,
                eventTimeFormat: { hour: 'numeric', minute: '2-digit' },
            });

            calendar.render();
        });
    </script>
</x-filament-panels::page>
