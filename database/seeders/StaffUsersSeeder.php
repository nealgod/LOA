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

        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@evsu.edu.ph',
                'role' => UserRole::Administrator,
                'department_id' => null,
            ],
            [
                'name' => 'DCS Department Head',
                'email' => 'dcs.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dcs?->id,
            ],
            [
                'name' => 'DTE Department Head',
                'email' => 'dte.head@evsu.edu.ph',
                'role' => UserRole::DepartmentHead,
                'department_id' => $dte?->id,
            ],
            [
                'name' => 'SASO Officer',
                'email' => 'saso@evsu.edu.ph',
                'role' => UserRole::SasoOfficer,
                'department_id' => null,
            ],
            [
                'name' => 'Campus Director',
                'email' => 'director@evsu.edu.ph',
                'role' => UserRole::CampusDirector,
                'department_id' => null,
            ],
            [
                'name' => 'Registrar',
                'email' => 'registrar@evsu.edu.ph',
                'role' => UserRole::Registrar,
                'department_id' => null,
            ],
            [
                'name' => 'Guidance Counselor',
                'email' => 'guidance@evsu.edu.ph',
                'role' => UserRole::Guidance,
                'department_id' => null,
            ],
        ];

        foreach ($users as $record) {
            if (User::query()->where('email', $record['email'])->exists()) {
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
