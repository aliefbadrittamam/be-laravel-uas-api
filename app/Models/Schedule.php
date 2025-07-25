<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'date',
        'start_time',
        'end_time',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i'
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    public function getDurationInHours()
    {
        $start = Carbon::createFromFormat('H:i', $this->start_time);
        $end = Carbon::createFromFormat('H:i', $this->end_time);
        
        return $end->diffInHours($start);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}