<?php

namespace App\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case DepartmentHead = 'department_head';
    case SasoOfficer = 'saso_officer';
    case CampusDirector = 'campus_director';
    case Registrar = 'registrar';
    case Guidance = 'guidance';
    case DirectorsOffice = 'directors_office';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'System Administrator',
            self::DepartmentHead => 'Department Head',
            self::SasoOfficer => 'SASO Officer',
            self::CampusDirector => 'Campus Director',
            self::Registrar => 'Registrar',
            self::Guidance => 'Guidance Office',
            self::DirectorsOffice => "Director's Office",
        };
    }

    public function needsDepartment(): bool
    {
        return $this === self::DepartmentHead;
    }

    /** Student is separate. These five are the staff offices on register/login. */
    public static function staffRoles(): array
    {
        return [
            self::DepartmentHead,
            self::SasoOfficer,
            self::CampusDirector,
            self::Registrar,
            self::Guidance,
        ];
    }
}
