<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            ['shift_name' => 'GU', 'schedule_in' => '08:00', 'schedule_out' => '16:00'],
            ['shift_name' => 'GU1', 'schedule_in' => '09:00', 'schedule_out' => '17:00'],
            ['shift_name' => 'OS', 'schedule_in' => '08:00', 'schedule_out' => '12:00'],
            ['shift_name' => 'HO', 'schedule_in' => '08:00', 'schedule_out' => '16:30'],
            ['shift_name' => 'dayoff', 'schedule_in' => '00:00', 'schedule_out' => '00:00'],
        ];

        DB::table('shifts')->insert($shifts);
    }
}
