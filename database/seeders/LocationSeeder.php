<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        Location::create([
            'name' => 'Kantor Pusat',
            'code' => 'KP-001',
            'address' => 'Jl. Jenderal Sudirman No. 1',
            'latitude' => -6.208763,
            'longitude' => 106.845599,
            'radius_meters' => 100,
        ]);

        Location::create([
            'name' => 'Ruang IT',
            'code' => 'R-IT',
            'address' => 'Gedung B, Lantai 3',
            'radius_meters' => 50,
        ]);
    }
}
