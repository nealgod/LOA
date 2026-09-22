@extends('layouts.staff')

@section('eyebrow', 'User Management')
@section('title', 'Invite New User')

@section('content')
<div class="mx-auto max-w-xl">
    <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm sm:p-8">

        <p class="mb-6 text-sm text-maroon-900/60">
            Fill in the details below. An invitation email will be sent to the address you enter.
            The recipient clicks the link to set their password and activate their account.
        </p>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
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
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('email') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-maroon-900">Role</label>
                <select id="role" name="role" required data-role-select
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    <option value="" disabled @selected(! old('role'))>Select role</option>
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
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="rounded-xl bg-maroon-800 px-5 py-2.5 text-sm font-semibold text-cream-50 hover:bg-maroon-700">
                    Send Invitation
                </button>
                <a href="{{ route('admin.users.index') }}"
                   class="rounded-xl border border-maroon-900/20 bg-white px-5 py-2.5 text-sm font-semibold text-maroon-700 hover:bg-maroon-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
