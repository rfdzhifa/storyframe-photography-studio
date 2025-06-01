<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Booking Management';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('booking_code')->disabled(),
            Forms\Components\TextInput::make('customer_name')->required(),
            Forms\Components\TextInput::make('customer_email')->email()->required(),
            Forms\Components\TextInput::make('customer_phone')->required(),
            Forms\Components\Select::make('service_id')->relationship('service', 'name')->required(),
            Forms\Components\Select::make('package_id')->relationship('package', 'name')->required(),
            Forms\Components\DatePicker::make('booking_date')->required(),
            Forms\Components\TimePicker::make('start_time')->required(),
            Forms\Components\TimePicker::make('end_time')->required(),
            Forms\Components\TextInput::make('total_price')->numeric()->required(),
            Forms\Components\Textarea::make('notes'),
            Forms\Components\Select::make('booking_status_id')
                ->relationship('bookingStatus', 'name')->required(),
            Forms\Components\Select::make('payment_option')
                ->options([
                    'dp' => 'DP',
                    'full' => 'Full Payment',
                ])->required(),
            Forms\Components\TextInput::make('down_payment_amount')->numeric()->nullable(),
            Forms\Components\Select::make('payment_status')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')->searchable(),
                Tables\Columns\TextColumn::make('customer_name')->searchable(),
                Tables\Columns\TextColumn::make('bookingStatus.name')->label('Status'),
                Tables\Columns\TextColumn::make('service.name')->label('Service'),
                Tables\Columns\TextColumn::make('package.name')->label('Package'),
                Tables\Columns\TextColumn::make('booking_date'),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('total_price')->money('IDR'),
                Tables\Columns\TextColumn::make('payment_option')->label('Payment Options'),
                Tables\Columns\TextColumn::make('payment_status')->label('Payment Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}