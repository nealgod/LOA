<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345678');

        $dcs = Department::query()->where('code', 'DCS')->first();
        $dte = Department::query()->where('code', 'DTE')->first();
        $htm = Department::query()->where('code', 'HTM')->orWhere('code', 'DBM')->first();
        $dit = Department::query()->where('code', 'DIT')->first();

        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@evsu.edu.ph',
                'role' => UserRole::Administrator,
                'department_id' => null,
            ],
            [
                'name' => 'Engr. Joseph Jaymel S. Morpos, MSIT',
                'email' => 'dcs.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dcs?->id,
            ],
            [
                'name' => 'Dr. Guillermo M. Sodomia',
                'email' => 'dte.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dte?->id,
            ],
            [
                'name' => 'Prof. Lyra Calvez',
                'email' => 'htm.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $htm?->id,
            ],
            [
                'name' => 'Prof. Alan Reynaldo E. Mabitad',
                'email' => 'dit.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dit?->id,
            ],
            [
                'name' => 'Dr. Joergen T. Arradaza, Jr.',
                'email' => 'saso@evsu.edu.ph',
                'role' => UserRole::SasoOfficer,
                'department_id' => null,
            ],
            [
                'name' => 'Dr. Maricel A. Gomez',
                'email' => 'director@evsu.edu.ph',
                'role' => UserRole::CampusDirector,
                'department_id' => null,
            ],
            [
                'name' => 'Jonnah R. Benitez, MAEd',
                'email' => 'registrar@evsu.edu.ph',
                'role' => UserRole::Registrar,
                'department_id' => null,
            ],
            [
                'name' => 'Charlene Pita',
                'email' => 'guidance@evsu.edu.ph',
                'role' => UserRole::Guidance,
                'department_id' => null,
            ],
        ];

        foreach ($users as $record) {
            $user = User::query()->where('email', $record['email'])->first();

            if ($user) {
                $user->update([
                    'name'          => $record['name'],
                    'role'          => $record['role'],
                    'department_id' => $record['department_id'],
                ]);
                continue;
            }

            User::query()->create([
                'name'                    => $record['name'],
                'email'                   => $record['email'],
                'password'                => $password,
                'role'                    => $record['role'],
                'department_id'           => $record['department_id'],
                // Seeded accounts are pre-activated — no invitation flow needed.
                'invitation_accepted_at'  => now(),
                'email_verified_at'       => now(),
            ]);
        }
    }
}
