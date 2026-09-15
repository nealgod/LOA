<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'roles' => UserRole::staffRoles(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = UserRole::tryFrom((string) $request->input('role'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(array_map(fn (UserRole $role) => $role->value, UserRole::staffRoles()))],
            'department_id' => [
                Rule::requiredIf($role?->needsDepartment() ?? false),
                'nullable',
                'exists:departments,id',
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'department_id' => $role?->needsDepartment() ? $validated['department_id'] : null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('staff.dashboard');
    }
}
