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
        'start_time' => 'string', // Changed from datetime:H:i to string
        'end_time' => 'string'    // Changed from datetime:H:i to string
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
        try {
            // Parse time strings directly
            $start = Carbon::createFromFormat('H:i:s', $this->start_time);
            $end = Carbon::createFromFormat('H:i:s', $this->end_time);
            
            // If end time is before start time, assume it's next day
            if ($end->lt($start)) {
                $end->addDay();
            }
            
            return $end->diffInHours($start);
        } catch (\Exception $e) {
            // Fallback: try with H:i format
            try {
                $start = Carbon::createFromFormat('H:i', $this->start_time);
                $end = Carbon::createFromFormat('H:i', $this->end_time);
                
                if ($end->lt($start)) {
                    $end->addDay();
                }
                
                return $end->diffInHours($start);
            } catch (\Exception $e2) {
                // Return default duration if parsing fails
                return 1;
            }
        }
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // Accessor untuk format time yang konsisten
    public function getStartTimeAttribute($value)
    {
        if (!$value) return $value;
        
        // Jika sudah format H:i, return as is
        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value;
        }
        
        // Jika format H:i:s, potong detiknya
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
            return substr($value, 0, 5);
        }
        
        return $value;
    }

    public function getEndTimeAttribute($value)
    {
        if (!$value) return $value;
        
        // Jika sudah format H:i, return as is
        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value;
        }
        
        // Jika format H:i:s, potong detiknya
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
            return substr($value, 0, 5);
        }
        
        return $value;
    }
}