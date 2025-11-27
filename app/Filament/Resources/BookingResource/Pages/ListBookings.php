<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('list')
                ->label('List View')
                ->icon('heroicon-o-list-bullet')
                ->color('primary')
                ->disabled(),

            // Tombol ke halaman Calendar
            Actions\Action::make('calendar')
                ->label('Calendar View')
                ->icon('heroicon-o-calendar')
                ->url(fn () => BookingResource::getUrl('calendar'))
                ->button(),
        ];
    }
}
