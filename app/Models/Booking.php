<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'total_price',
        'notes'
    ];

    protected $casts = [
        'total_price' => 'decimal:2'
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}