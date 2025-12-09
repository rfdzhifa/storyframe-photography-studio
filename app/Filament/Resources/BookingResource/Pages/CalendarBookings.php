<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\Page;

class CalendarBookings extends Page
{
    protected static string $resource = BookingResource::class;

    protected static string $view = 'filament.resources.booking-resource.pages.calendar-bookings';

    public array $events = [];

    public function mount(): void
    {
        // Ambil semua booking dan ubah ke event FullCalendar
        $this->events = Booking::notExpired()
        ->orderBy('booking_date')
        ->orderBy('start_time')
        ->get()
        ->map(function (Booking $booking) {
            $date  = Carbon::parse($booking->booking_date)->format('Y-m-d');
            $start = Carbon::parse($booking->start_time)->format('H:i:s');
            $end   = Carbon::parse($booking->end_time)->format('H:i:s');

            return [
                'title' => sprintf(
                    '%s - %s | %s',
                    Carbon::parse($booking->start_time)->format('H:i'),
                    Carbon::parse($booking->end_time)->format('H:i'),
                    $booking->customer_name,
                ),
                'start' => "{$date}T{$start}",
                'end'   => "{$date}T{$end}",

                'extendedProps' => [
                    'booking_code'   => $booking->booking_code,
                    'customer_name'  => $booking->customer_name,
                    'service'        => optional($booking->service)->name,
                    'package'        => optional($booking->package)->name,
                    'status'         => optional($booking->bookingStatus)->name,
                    'payment_status' => $booking->payment_status,
                    'booking_date'   => Carbon::parse($booking->booking_date)->format('d M Y'),
                    'start_time'     => Carbon::parse($booking->start_time)->format('H:i'),
                    'end_time'       => Carbon::parse($booking->end_time)->format('H:i'),
                    'edit_url'       => BookingResource::getUrl('edit', ['record' => $booking]),
                ],
            ];
        })
        ->values()
        ->toArray();
        }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('list')
                ->label('List View')
                ->icon('heroicon-o-list-bullet')
                ->url(fn () => BookingResource::getUrl('index'))
                ->button(),

            Actions\Action::make('calendar')
                ->label('Calendar View')
                ->icon('heroicon-o-calendar')
                ->color('primary')
                ->disabled(),
        ];
    }
}
