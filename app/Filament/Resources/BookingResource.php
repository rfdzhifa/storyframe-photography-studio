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
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            Forms\Components\Select::make('service_id')
            ->label('Service')
            ->relationship('service', 'name')
            ->required()
            ->reactive(),
            Forms\Components\Select::make('package_id')
            ->label('Package')
            ->options(function (callable $get) {
                $serviceId = $get('service_id');
                if (!$serviceId) return [];
        
                // Ambil package dari pivot table
                return \DB::table('service_packages')
                    ->join('packages', 'packages.id', '=', 'service_packages.package_id')
                    ->where('service_packages.service_id', $serviceId)
                    ->where('service_packages.is_active', true)
                    ->pluck('packages.name', 'packages.id');
            })
            ->required()
            ->reactive()
            ->afterStateUpdated(fn (callable $get, callable $set) => $set('total_price', self::getPrice($get('service_id'), $get('package_id')))),
            Forms\Components\DatePicker::make('booking_date')
            ->label('Tanggal Booking')
            ->required()
            ->reactive()
            ->minDate(now()) // hari ini
            ->maxDate(now()->addDays(30)) // max 30 hari ke depan
            ->native(false),
            Forms\Components\Select::make('time_slot')
    ->label('Jam Booking')
    ->options(function (callable $get) {
        $serviceId = $get('service_id');
        $packageId = $get('package_id');
        $date = $get('booking_date');

        if (!$serviceId || !$packageId || !$date) {
            return [];
        }

        $dateOnly = Carbon::parse($date)->format('Y-m-d'); // 💉 fix

        $package = \App\Models\Package::find($packageId);
        $duration = $package?->duration ?? 30;

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $schedule = \App\Models\WeeklySchedule::where('day_of_week', $dayOfWeek)->first();
        if (!$schedule) return [];

        $start = Carbon::parse("$dateOnly {$schedule->start_time}");
        $end = Carbon::parse("$dateOnly {$schedule->end_time}");

        $existingBookings = Booking::where('booking_date', $dateOnly)
            ->where('service_id', $serviceId)
            ->get();

        $slots = [];
        while ($start->lt($end)) {
            $slotEnd = (clone $start)->addMinutes($duration);

            $isBooked = $existingBookings->first(function ($b) use ($start, $slotEnd) {
                return Carbon::parse($b->start_time)->lt($slotEnd)
                    && Carbon::parse($b->end_time)->gt($start);
            });

            if (!$isBooked) {
                $label = $start->format('H:i') . ' - ' . $slotEnd->format('H:i');
                $slots[$start->format('H:i:s')] = $label;
            }

            $start->addMinutes($duration);
        }

        return $slots;
    })
    ->required()
    ->disabled(fn (callable $get) => !$get('service_id') || !$get('package_id') || !$get('booking_date'))
    ->hint('Slot otomatis tergenerate dari jadwal dan booking yang ada')
    ->hidden(fn (callable $get) => !$get('booking_date')),

            Forms\Components\TextInput::make('total_price')
            ->label('Total Price (IDR)')
            ->numeric()
            ->disabled()
            ->required(),
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
    
    protected static function getPrice($serviceId, $packageId)
    {
        if (!$serviceId || !$packageId) return null;

        $price = DB::table('service_packages')
            ->where('service_id', $serviceId)
            ->where('package_id', $packageId)
            ->value('price');  // asumsikan ada kolom 'price' di service_packages

        return $price ?? null;
    }
    
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['booking_code'])) {
            $data['booking_code'] = 'BOOK-' . strtoupper(Str::random(8));
        }
        
        if (isset($data['booking_date'], $data['time_slot'], $data['package_id'])) {
            $bookingDate = Carbon::parse($data['booking_date']);
            $bookingDateOnly = $bookingDate->format('Y-m-d');

            // 🔐 Fix parsing bug
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $bookingDateOnly . ' ' . $data['time_slot']);

            $duration = \App\Models\Package::find($data['package_id'])?->duration ?? 30;
            $end = (clone $start)->addMinutes($duration);

            $data['start_time'] = $start->format('H:i:s');
            $data['end_time'] = $end->format('H:i:s');
        }

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return static::mutateFormDataBeforeCreate($data);
    }
}