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
        $this->events = Booking::query()
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get()
            ->map(function (Booking $booking) {
                // booking_date = date, start_time & end_time = time
                $date = Carbon::parse($booking->booking_date)->format('Y-m-d');
                $start = Carbon::parse($booking->start_time)->format('H:i:s');
                $end   = Carbon::parse($booking->end_time)->format('H:i:s');

                return [
                    'title' => Carbon::parse($booking->start_time)->format('H:i') . ' ' . $booking->booking_code,
                    'start' => "{$date}T{$start}",
                    'end'   => "{$date}T{$end}",
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            // balik ke tabel
            Actions\Action::make('board')
                ->label('Board')
                ->icon('heroicon-o-view-columns')
                ->url(fn () => BookingResource::getUrl('index'))
                ->button(),

            // halaman sekarang = calendar
            Actions\Action::make('calendar')
                ->label('Calendar')
                ->icon('heroicon-o-calendar')
                ->color('primary')
                ->disabled(),
        ];
    }
}
