<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TemplateSeeder extends Seeder
{
    /**
     * Seed templates from Excel sheets
     * Based on Access_matrix_Review_Allocation.xlsx
     */
    public function run(): void
    {
        // Get department IDs
        $physicianDept = DB::table('departments')->where('code', 'PHYSICIAN')->first();
        
        // Physician Department Templates (from Excel)
        $physicianTemplates = [
            ['TEMPDENT00', 'DENTIST'],
            ['TEMPPHYS00', 'Template, Physician'],
            ['TEMPPHYS01', 'Template, Resident/Medical Officer'],
            ['TEMPPHYS02', 'Template, Student'],
            ['TEMPPHYS03', 'Template, Physician Surgical'],
            ['TEMPPHYS04', 'Template, Physician A&E'],
            ['TEMPPHYS05', 'Template, Physician AMB'],
            ['TEMPPHYS06', 'Template, Physician Oncology'],
            ['TEMPPHYS07', 'Surgery Template, Resident Medical Officers'],
            ['TEMPPHYS08', 'Oncology Template, Resident Medical Officers'],
            ['TEMPPHYS09', 'AMB Template Resident Medical Officer'],
            ['TEMPPHYS10', 'Template, Resident Medical Off A&E'],
            ['TEMPPHYS11', 'Template, Physician Medicine'],
            ['TEMPPHYS12', 'Template Physician pediatrics'],
            ['TEMPPHYS13', 'Obs Gyne Template Physician'],
            ['TEMPPHYS14', 'Peds(Bk) Template Physician'],
            ['TEMPPHYS15', 'Template Physician Instructor'],
            ['TEMPPHYS16', 'Template Physician Associate'],
            ['TEMPPHYS17', 'Template, Physician (OncLab)'],
            ['TEMPPHYS18', 'Template, Physician Risk Manager'],
            ['TEMPPHYS19', 'Template, Physician Fellow'],
            ['TEMPPHYS20', 'Template, Medical Intern'],
        ];

        foreach ($physicianTemplates as $template) {
            DB::table('templates')->insert([
                'mnemonic' => $template[0],
                'name' => $template[1],
                'department_id' => $physicianDept->id,
                'category' => 'Physician',
                'ehr_access_level' => 'full',
                'is_active' => true,
                'requires_cos_approval' => true,
                'version' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info('✓ Seeded ' . count($physicianTemplates) . ' Physician templates');

        // Add templates for other departments as needed
        
    }
}