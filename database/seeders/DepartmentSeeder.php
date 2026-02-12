<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed departments from Excel sheet tabs
     */
    public function run(): void
    {
        $departments = [
            ['code' => 'PHYSICIAN', 'name' => 'Physician', 'description' => 'Physician department - doctors and medical officers'],
            ['code' => 'NURSE', 'name' => 'Nurse', 'description' => 'Nursing department - all nursing staff'],
            ['code' => 'SURGERY', 'name' => 'Surgery', 'description' => 'Surgical department'],
            ['code' => 'PHARMACY', 'name' => 'Pharmacy', 'description' => 'Pharmacy department'],
            ['code' => 'LAB', 'name' => 'Lab', 'description' => 'Laboratory department'],
            ['code' => 'RADIOLOGY', 'name' => 'Radiology', 'description' => 'Radiology and imaging department'],
            ['code' => 'DIETICIAN', 'name' => 'Dietician', 'description' => 'Dietician and nutrition department'],
            ['code' => 'PHYSIO', 'name' => 'Physio', 'description' => 'Physiotherapy department'],
            ['code' => 'MM', 'name' => 'MM', 'description' => 'Materials Management department'],
            ['code' => 'FINANCE', 'name' => 'Finance', 'description' => 'Finance and billing department'],
            ['code' => 'DENTAL', 'name' => 'Dental', 'description' => 'Dental department'],
            ['code' => 'HIM', 'name' => 'HIM', 'description' => 'Health Information Management'],
            ['code' => 'MARKETING', 'name' => 'Marketing', 'description' => 'Marketing and communications'],
            ['code' => 'ONC', 'name' => 'ONC', 'description' => 'Oncology department'],
            ['code' => 'QRM', 'name' => 'QRM', 'description' => 'Quality and Risk Management'],
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insert([
                'code' => $dept['code'],
                'name' => $dept['name'],
                'description' => $dept['description'],
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('✓ Seeded ' . count($departments) . ' departments');
    }
}