@extends('layouts.staff')

@section('eyebrow', 'User Management')
@section('title', 'Edit User — ' . $invitee->name)

@section('content')
<div class="mx-auto max-w-xl">
    <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 flex items-center gap-3">
            @php
                $parts    = explode(' ', trim($invitee->name));
                $initials = strtoupper(($parts[0][0] ?? 'U') . (end($parts)[0] ?? ''));
            @endphp
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-maroon-700 text-sm font-bold uppercase text-cream-50">
                {{ $initials }}
            </div>
            <div>
                <p class="font-semibold text-maroon-950">{{ $invitee->name }}</p>
                <p class="text-sm text-maroon-900/60">{{ $invitee->email }}</p>
            </div>
            <div class="ml-auto">
                @if ($invitee->isActivated())
                    <span class="rounded-full border border-emerald-600 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Active</span>
                @else
                    <span class="rounded-full border border-amber-500 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending invite</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $invitee) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-maroon-900">Full name</label>
                <input id="name" name="name" value="{{ old('name', $invitee->name) }}" required
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                @error('name') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-maroon-900">Role</label>
                <select id="role" name="role" required data-role-select
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $invitee->role->value) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                @error('role') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>

            <div @class(['hidden' => ! $invitee->role->needsDepartment()]) data-department-wrap>
                <label for="department_id" class="block text-sm font-medium text-maroon-900">Department</label>
                <select id="department_id" name="department_id"
                        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">
                    <option value="">Select department</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id', $invitee->department_id) == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="rounded-xl bg-maroon-800 px-5 py-2.5 text-sm font-semibold text-cream-50 hover:bg-maroon-700">
                    Save Changes
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
