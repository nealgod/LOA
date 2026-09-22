@extends('layouts.staff')

@section('eyebrow', 'Administrator')
@section('title', 'User Management')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-maroon-900/60">All staff accounts — only the Administrator can add, edit, or remove users.</p>
        <a href="{{ route('admin.users.create') }}"
           class="rounded-xl bg-maroon-800 px-4 py-2 text-sm font-semibold text-cream-50 hover:bg-maroon-700">
            + Invite User
        </a>
    </div>

    {{-- Flashed invite link (shown when email fails or admin uses Copy Link) --}}
    @if (session('invite_link'))
        <div class="mb-6 rounded-xl border border-amber-400 bg-amber-50 p-4">
            <p class="mb-2 text-sm font-semibold text-amber-800">
                Setup link for {{ session('invite_link_for') }} — copy and share this manually:
            </p>
            <div class="flex items-center gap-2">
                <input id="invite-link-input"
                       type="text"
                       readonly
                       value="{{ session('invite_link') }}"
                       class="flex-1 rounded-lg border border-amber-300 bg-white px-3 py-2 font-mono text-xs text-amber-900 outline-none">
                <button onclick="navigator.clipboard.writeText(document.getElementById('invite-link-input').value).then(()=>this.textContent='Copied!')"
                        class="rounded-lg border border-amber-400 bg-white px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                    Copy
                </button>
            </div>
            <p class="mt-2 text-xs text-amber-700">This link expires in 48 hours. Opening it will invalidate any previous link for this user.</p>
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-maroon-900/10 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-maroon-900/10">
            <thead class="bg-maroon-950/5">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Department</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-maroon-900/70">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-maroon-900/10">
                @forelse ($users as $person)
                    <tr class="hover:bg-maroon-950/[0.02]">
                        <td class="px-5 py-3 text-sm font-medium text-maroon-950">{{ $person->name }}</td>
                        <td class="px-5 py-3 text-sm text-maroon-900/80">{{ $person->email }}</td>
                        <td class="px-5 py-3 text-sm text-maroon-900/80">{{ $person->role->label() }}</td>
                        <td class="px-5 py-3 text-sm text-maroon-900/80">{{ $person->department?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-sm">
                            @if ($person->isActivated())
                                <span class="rounded-full border border-emerald-600 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">Active</span>
                            @else
                                <span class="rounded-full border border-amber-500 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Pending invite</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-sm">
                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $person) }}"
                                   class="rounded-md border border-maroon-900/20 bg-white px-3 py-1.5 text-xs font-semibold text-maroon-700 hover:bg-maroon-50">
                                    Edit
                                </a>

                                @if (! $person->isActivated())
                                    {{-- Resend email --}}
                                    <form method="POST" action="{{ route('admin.users.resend', $person) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-md border border-blue-500 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                            Resend Email
                                        </button>
                                    </form>
                                    {{-- Copy link (fallback if email fails) --}}
                                    <form method="POST" action="{{ route('admin.users.get-link', $person) }}">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-md border border-amber-500 bg-white px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-50">
                                            Get Link
                                        </button>
                                    </form>
                                @endif

                                @if ($person->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $person) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($person->name) }}? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md border border-red-500 bg-white px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-maroon-900/50">No users yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
