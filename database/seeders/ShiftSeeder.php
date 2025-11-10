<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Shift::create([
            'name' => 'Day Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        Shift::create([
            'name' => 'Night Shift',
            'start_time' => '22:00:00',
            'end_time' => '07:00:00',
        ]);
    }
}
