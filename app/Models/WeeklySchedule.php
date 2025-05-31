<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class WeeklySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];
    
    public function generateSlots($date, $durationInMinutes)
{
    $slots = [];

    $start = Carbon::parse($date . ' ' . $this->start_time);
    $end = Carbon::parse($date . ' ' . $this->end_time);

    while ($start->copy()->addMinutes($durationInMinutes) <= $end) {
        $slots[] = [
            'start' => $start->format('H:i'),
            'end' => $start->copy()->addMinutes($durationInMinutes)->format('H:i'),
        ];
        $start->addMinutes($durationInMinutes);
    }

    return $slots;
}
}