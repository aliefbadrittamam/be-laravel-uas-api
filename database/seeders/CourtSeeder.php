<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Court;

class CourtSeeder extends Seeder
{
    public function run()
    {
        $courts = [
            [
                'name' => 'Lapangan A',
                'description' => 'Lapangan badminton premium dengan lantai kayu',
                'price_per_hour' => 50000,
                'status' => 'active'
            ],
            [
                'name' => 'Lapangan B',
                'description' => 'Lapangan badminton standar dengan fasilitas lengkap',
                'price_per_hour' => 40000,
                'status' => 'active'
            ],
            [
                'name' => 'Lapangan C',
                'description' => 'Lapangan badminton ekonomis untuk latihan',
                'price_per_hour' => 30000,
                'status' => 'active'
            ]
        ];

        foreach ($courts as $court) {
            Court::create($court);
        }
    }
}