<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            [
                'code' => 'DCS',
                'name' => 'Department of Computer Studies',
                'programs' => [
                    ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology (BSIT)'],
                ],
            ],
            [
                'code' => 'DTE',
                'name' => 'Department of Teacher Education',
                'programs' => [
                    ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education (BEED)'],
                    ['code' => 'BSED-MATH', 'name' => 'Bachelor of Secondary Education (BSEd) major in Mathematics'],
                    ['code' => 'BSED-SCI', 'name' => 'Bachelor of Secondary Education (BSEd) major in Science'],
                    ['code' => 'BPED', 'name' => 'Bachelor of Physical Education (BPEd)'],
                    ['code' => 'BTVTED', 'name' => 'Bachelor of Technical-Vocational Teacher Education (BTVTEd)'],
                ],
            ],
            [
                'code' => 'DBM',
                'name' => 'Department of Business Management',
                'programs' => [
                    ['code' => 'BSHM', 'name' => 'Bachelor of Science in Hospitality Management (BSHM)'],
                ],
            ],
            [
                'code' => 'DOE',
                'name' => 'Department of Engineering',
                'programs' => [
                    ['code' => 'BSCE', 'name' => 'Bachelor of Science in Civil Engineering (BSCE)'],
                    ['code' => 'BSEE', 'name' => 'Bachelor of Science in Electrical Engineering (BSEE)'],
                    ['code' => 'BSME', 'name' => 'Bachelor of Science in Mechanical Engineering (BSME)'],
                ],
            ],
            [
                'code' => 'DIT',
                'name' => 'Department of Industrial Technology',
                'programs' => [
                    ['code' => 'BIT-CA', 'name' => 'Bachelor of Industrial Technology (BIT) major in Culinary Arts (CA)'],
                    ['code' => 'BIT-ET', 'name' => 'Bachelor of Industrial Technology (BIT) major in Electronics (ET)'],
                ],
            ],
        ];

        Program::query()->delete();

        foreach ($catalog as $entry) {
            $department = Department::query()->updateOrCreate(
                ['code' => $entry['code']],
                ['name' => $entry['name']],
            );

            foreach ($entry['programs'] as $program) {
                Program::query()->create([
                    'department_id' => $department->id,
                    'code' => $program['code'],
                    'name' => $program['name'],
                ]);
            }
        }

        Department::query()
            ->whereNotIn('code', collect($catalog)->pluck('code'))
            ->delete();
    }
}
