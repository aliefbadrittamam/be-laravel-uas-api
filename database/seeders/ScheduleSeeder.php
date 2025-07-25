<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Court;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run()
    {
        $courts = Court::all();
        
        // Create schedules for next 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->addDays($i)->format('Y-m-d');
            
            foreach ($courts as $court) {
                // Morning schedules
                Schedule::create([
                    'court_id' => $court->id,
                    'date' => $date,
                    'start_time' => '08:00',
                    'end_time' => '10:00',
                    'status' => 'available'
                ]);
                
                Schedule::create([
                    'court_id' => $court->id,
                    'date' => $date,
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                    'status' => 'available'
                ]);
                
                // Afternoon schedules
                Schedule::create([
                    'court_id' => $court->id,
                    'date' => $date,
                    'start_time' => '14:00',
                    'end_time' => '16:00',
                    'status' => 'available'
                ]);
                
                Schedule::create([
                    'court_id' => $court->id,
                    'date' => $date,
                    'start_time' => '16:00',
                    'end_time' => '18:00',
                    'status' => 'available'
                ]);
            }
        }
    }
}