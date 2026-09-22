<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LoaRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        $acted = LoaRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('dept_head_by', $user->id)
                  ->orWhere('saso_by', $user->id)
                  ->orWhere('campus_director_by', $user->id)
                  ->orWhere('registrar_by', $user->id)
                  ->orWhere('guidance_by', $user->id)
                  ->orWhere('rejected_by', $user->id);
            })->count();

        $approved = LoaRequest::query()
            ->where(function ($q) use ($user) {
                $q->where('dept_head_by', $user->id)
                  ->orWhere('saso_by', $user->id)
                  ->orWhere('campus_director_by', $user->id)
                  ->orWhere('registrar_by', $user->id)
                  ->orWhere('guidance_by', $user->id);
            })->count();

        $rejected = LoaRequest::query()
            ->where('rejected_by', $user->id)
            ->count();

        return view('staff.profile', compact('user', 'acted', 'approved', 'rejected'));
    }

    public function updateName(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:2'],
        ]);

        $request->user()->update([
            'name' => trim($request->input('name')),
        ]);

        return back()->with('profile_status', 'Name updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password you entered is incorrect.'])
                ->withInput();
        }

        $user->update([
            'password' => $request->input('password'),
        ]);

        return back()->with('password_status', 'Password changed successfully.');
    }
}
