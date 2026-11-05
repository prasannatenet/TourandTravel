@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('header', 'Roles & Permissions')
@section('subheader', 'Create admin roles and choose the permissions assigned to each role.')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('created_account'))
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-900">
            Login created for <span class="font-semibold">{{ session('created_account.name') }}</span>.
            ID: <span class="font-semibold">{{ session('created_account.email') }}</span>.
            Use the password entered above to sign in at
            <a href="{{ route('admin.login') }}" class="font-semibold underline">{{ route('admin.login') }}</a>.
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="mb-6">
            <h2 class="text-lg font-bold text-slate-900">Create a role</h2>
            <p class="mt-1 text-sm text-slate-500">Choose which dashboard sections this role can access. Dashboard access is always included.</p>
        </div>

        <form method="POST" action="{{ route('admin.roles-permissions.create-role') }}" class="space-y-6">
            @csrf

            <div class="max-w-xl">
                <label for="role-name" class="mb-1.5 block text-sm font-semibold text-slate-800">Role name</label>
                <input
                    id="role-name"
                    name="name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('name') }}"
                    placeholder="e.g. Booking Manager"
                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100"
                >
            </div>

            <fieldset class="space-y-5">
                <legend class="mb-3 text-sm font-semibold text-slate-800">Dashboard permissions</legend>
                @foreach ($permissionGroups as $module => $group)
                    <div>
                        <h3 class="mb-2 text-sm font-semibold text-slate-700">{{ $group['label'] }}</h3>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($group['permissions'] as $permission)
                                <label for="permission-{{ $permission->id }}" class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-indigo-300 hover:bg-indigo-50/50">
                                    @if ($permission->name === 'dashboard.view')
                                        <input type="hidden" name="permissions[]" value="{{ $permission->id }}">
                                        <input id="permission-{{ $permission->id }}" type="checkbox" checked disabled class="mt-0.5 rounded border-slate-300 text-indigo-600">
                                    @else
                                        <input
                                            id="permission-{{ $permission->id }}"
                                            name="permissions[]"
                                            type="checkbox"
                                            value="{{ $permission->id }}"
                                            @checked(in_array((string) $permission->id, (array) old('permissions', []), true))
                                            class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-slate-800">{{ $group['label'] }}: {{ $permission->name === 'dashboard.view' ? 'Always included' : \Illuminate\Support\Str::headline(\Illuminate\Support\Str::afterLast($permission->name, '.')) }}</span>
                                        @if ($permission->name === 'dashboard.view')
                                            <span class="mt-0.5 block text-xs text-slate-500">Every role can open the dashboard home page.</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </fieldset>

            <section class="space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Create a login for this role <span class="font-normal text-slate-500">(optional)</span></h3>
                    <p class="mt-1 text-xs text-slate-500">Enter an email and password to create a manager account and assign it to this role.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="account-name" class="mb-1.5 block text-sm font-semibold text-slate-800">Account name</label>
                        <input id="account-name" name="account_name" type="text" maxlength="255" value="{{ old('account_name') }}" autocomplete="name" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label for="account-email" class="mb-1.5 block text-sm font-semibold text-slate-800">Login ID (email)</label>
                        <input id="account-email" name="account_email" type="email" maxlength="255" value="{{ old('account_email') }}" autocomplete="email" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label for="account-password" class="mb-1.5 block text-sm font-semibold text-slate-800">Password</label>
                        <input id="account-password" name="account_password" type="password" minlength="8" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div>
                        <label for="account-password-confirmation" class="mb-1.5 block text-sm font-semibold text-slate-800">Confirm password</label>
                        <input id="account-password-confirmation" name="account_password_confirmation" type="password" minlength="8" autocomplete="new-password" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    </div>
                </div>
            </section>

            <div class="border-t border-slate-100 pt-5">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                    <i class="fa-solid fa-plus"></i>
                    Create role
                </button>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-7">
            <h2 class="font-bold text-slate-900">Existing roles</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-3 sm:px-7">Role</th>
                        <th class="px-5 py-3">Permissions</th>
                        <th class="px-5 py-3 text-right sm:px-7">Users</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($roles as $role)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-900 sm:px-7">{{ $role->name }}</td>
                            <td class="px-5 py-4">
                                @forelse ($role->permissions as $permission)
                                    <span class="mb-1 mr-1 inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">{{ $permission->name }}</span>
                                @empty
                                    <span class="text-slate-500">No permissions assigned</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-4 text-right text-slate-600 sm:px-7">{{ $role->users_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-slate-500 sm:px-7">No roles created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
