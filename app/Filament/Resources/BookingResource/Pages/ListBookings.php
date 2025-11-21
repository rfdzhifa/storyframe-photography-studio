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
            // Halaman sekarang = Board, jadi tombol Board aktif
            Actions\Action::make('board')
                ->label('Board')
                ->icon('heroicon-o-view-columns')
                ->color('primary')
                ->disabled(), // lagi di board, jadi non-klik

            // Tombol ke halaman Calendar
            Actions\Action::make('calendar')
                ->label('Calendar')
                ->icon('heroicon-o-calendar')
                ->url(fn () => BookingResource::getUrl('calendar'))
                ->button(),
        ];
    }
}
