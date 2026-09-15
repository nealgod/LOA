@extends('layouts.app')

@section('title', 'Staff register')

@section('content')
<x-page-shell eyebrow="Staff" title="Staff register">
    <x-slot:lead>
        Create an employee account for the administrative portal. Later this will be checked against CampusPresence roles.
    </x-slot:lead>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4 rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-maroon-900">Full name</label>
            <input id="name" name="name" value="{{ old('name') }}" required
                placeholder="Lastname, Firstname, Middlename"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
            @error('name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-maroon-900">EVSU email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                placeholder="name@evsu.edu.ph"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 outline-none ring-maroon-700 focus:ring-2 text-base">
            @error('email') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="role" class="block text-sm font-medium text-maroon-900">Office / role</label>
            <select id="role" name="role" required data-role-select
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                <option value="" disabled @selected(old('role') === null || old('role') === '')>Select office</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            @error('role') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>
        <div class="hidden" data-department-wrap>
            <label for="department_id" class="block text-sm font-medium text-maroon-900">Department</label>
            <select id="department_id" name="department_id"
                class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                <option value="">Select department</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            @error('department_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </div>
        <x-password-field id="password" name="password" label="Password" placeholder="Create a password" class="text-base">
            @error('password') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
        </x-password-field>
        <x-password-field id="password_confirmation" name="password_confirmation" label="Confirm password" placeholder="Re-enter your password" class="text-base" />
        <button type="submit" class="w-full rounded-xl bg-maroon-800 px-4 py-3.5 text-base font-semibold text-cream-50 hover:bg-maroon-700">Create staff account</button>
        <p class="text-center text-sm text-maroon-800/80">Already registered? <a href="{{ route('login') }}" class="font-semibold text-maroon-700">Log in</a></p>
    </form>
</x-page-shell>
@endsection
