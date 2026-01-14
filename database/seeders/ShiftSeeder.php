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
            'lunch_break_start' => '12:00:00',
            'lunch_break_end' => '13:00:00',
            'lunch_break_duration' => 60,
            'is_lunch_paid' => false,
        ]);
    }
}
