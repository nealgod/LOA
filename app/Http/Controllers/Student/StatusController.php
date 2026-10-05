<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LoaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusController extends Controller
{
    public function show(Request $request): View
    {
        $loa      = null;
        $notFound = false;

        if ($request->isMethod('post')) {
            $request->validate([
                'control_number' => ['required', 'string', 'max:30'],
                'email'          => [
                    'required',
                    'email',
                    'max:255',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! str_ends_with(strtolower((string) $value), '@evsu.edu.ph')) {
                            $fail('Only official EVSU email addresses (@evsu.edu.ph) are accepted.');
                        }
                    },
                ],
            ], [
                'control_number.required' => 'Please enter your control number.',
                'email.required'          => 'Please enter your EVSU email address.',
            ]);

            // Both control number AND email must match — prevents accidental cross-student lookup.
            $loa = LoaRequest::query()
                ->with([
                    'department',
                    'program',
                    'deptHeadActor',
                    'sasoActor',
                    'campusDirectorActor',
                    'rejectedByActor',
                    'rejectionHistory.rejectedByActor',
                ])
                ->where('control_number', trim($request->input('control_number')))
                ->whereRaw('LOWER(email) = ?', [strtolower(trim($request->input('email')))])
                ->whereIn('status', ['submitted', 'approved', 'rejected'])
                ->first();

            if (! $loa) {
                $notFound = true;
            }
        }

        return view('student.status', compact('loa', 'notFound'));
    }
}
