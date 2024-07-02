<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read employees from CSV file
        $employeesCsvPath = database_path('seeders/data/employees.csv');
        $employees = array_map('str_getcsv', file($employeesCsvPath));
        $header = array_shift($employees);

        $defaultShiftId = DB::table('shifts')->value('id');

        foreach ($employees as $row) {
            $row = array_combine($header, $row);
            
            // Create employee
            DB::table('employees')->insert([
                'employee_id' => $row['Employee ID'],
                'full_name' => $row['Full Name'],
                'email' => $row['Email'],
                'organization' => $row['Organization'],
                'job_position' => $row['Job Position'],
                'job_level' => $row['Job Level'],
                'join_date' => Carbon::parse($row['Join Date']),
                'status_employee' => $row['Status Employee'],
                'birth_date' => Carbon::parse($row['Birth Date']),
                'age' => Carbon::parse($row['Birth Date'])->age,
                'birth_place' => $row['Birth Place'] ?? '-',
                'citizen_id_address' => $row['Citizen ID Address'] ?? '-',
                'residential_address' => $row['Residential Address'] ?? '-',
                'shift_id' => $defaultShiftId, // Assuming shift_id is always 1 in CSV, adjust accordingly
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
