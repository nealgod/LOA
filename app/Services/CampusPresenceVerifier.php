<?php

namespace App\Services;

class CampusPresenceVerifier
{
    /**
     * Stub until the real CampusPresence Supabase feed is connected.
     * Local development accepts any valid identity so the email-link flow can be tested.
     *
     * @return array{ok: bool, message?: string}
     */
    public function verifyStudent(string $studentId, string $fullName, string $email): array
    {
        if ($studentId === '' || $fullName === '' || $email === '') {
            return [
                'ok' => false,
                'message' => 'Student ID, full name, and official EVSU email are required.',
            ];
        }

        return ['ok' => true];
    }
}
