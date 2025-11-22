<x-filament-panels::page>
    {{-- KALENDAR --}}
    <div
        id="booking-calendar"
        wire:ignore
        style="min-height: 650px;"
    ></div>

    {{-- POPUP MODAL DETAIL BOOKING --}}
    <div
        id="booking-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40"
    >
        <div
            class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4"
            onclick="event.stopPropagation()" {{-- biar klik dalam card nggak nutup modal --}}
        >
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h2 class="text-lg font-semibold">
                    Detail Booking
                </h2>
                <button
                    type="button"
                    class="text-gray-500 hover:text-gray-700"
                    onclick="hideBookingModal()"
                >
                    ✕
                </button>
            </div>

            <div class="px-4 py-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-2 gap-x-6 text-sm">
                    <div>
                        <dt class="font-medium text-gray-700">Booking Code</dt>
                        <dd id="modal-booking-code" class="text-gray-900"></dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-700">Tanggal</dt>
                        <dd id="modal-booking-date" class="text-gray-900"></dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-700">Waktu</dt>
                        <dd id="modal-booking-time" class="text-gray-900"></dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-700">Customer</dt>
                        <dd id="modal-customer-name" class="text-gray-900"></dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-700">Service</dt>
                        <dd id="modal-service" class="text-gray-900"></dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-700">Package</dt>
                        <dd id="modal-package" class="text-gray-900"></dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-700">Status Booking</dt>
                        <dd id="modal-status" class="text-gray-900"></dd>
                    </div>

                    <div>
                        <dt class="font-medium text-gray-700">Payment Status</dt>
                        <dd id="modal-payment-status" class="text-gray-900"></dd>
                    </div>
                </dl>
            </div>

            <div class="flex justify-end gap-2 px-4 py-3 border-t">

    <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
        onclick="hideBookingModal()"
    >
        Tutup
    </button>
</div>
        </div>
    </div>

    {{-- FullCalendar CSS & JS --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css"
    >
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <script>
        // fungsi show / hide modal
        function showBookingModal(data) {
            const modal = document.getElementById('booking-modal');
            if (!modal) return;

            document.getElementById('modal-booking-code').textContent =
                data.booking_code ?? '-';

            document.getElementById('modal-booking-date').textContent =
                data.booking_date ?? '-';

            document.getElementById('modal-booking-time').textContent =
                (data.start_time && data.end_time)
                    ? `${data.start_time} - ${data.end_time}`
                    : '-';

            document.getElementById('modal-customer-name').textContent =
                data.customer_name ?? '-';

            document.getElementById('modal-service').textContent =
                data.service ?? '-';

            document.getElementById('modal-package').textContent =
                data.package ?? '-';

            document.getElementById('modal-status').textContent =
                data.status ?? '-';

            document.getElementById('modal-payment-status').textContent =
                data.payment_status ?? '-';


            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideBookingModal() {
            const modal = document.getElementById('booking-modal');
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('booking-calendar');
            const modal = document.getElementById('booking-modal');

            if (!el) return;

            // klik area gelap di belakang card -> close
            if (modal) {
                modal.addEventListener('click', function () {
                    hideBookingModal();
                });
            }

            const events = @json($events);
            console.log('booking events', events);

            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay',
                },
                events: events,
                displayEventTime: false, // kita pakai jam di title & modal

                eventClick(info) {
                    info.jsEvent.preventDefault();

                    const data = info.event.extendedProps || {};
                    showBookingModal(data);
                },
            });

            calendar.render();
        });
    </script>
</x-filament-panels::page>
