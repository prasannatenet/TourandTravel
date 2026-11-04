@extends('layouts.admin')

@section('title', 'Email Settings')
@section('header', 'Email Settings')
@section('subheader', 'Configure outgoing email delivery for your application.')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
            {{ session('error') }}
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

    <form method="POST" action="{{ route('admin.email-settings.update') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @csrf
        @method('PUT')

        <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-envelope-circle-check"></i>
                </span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">SMTP Configuration</h2>
                    <p class="mt-1 text-sm text-slate-500">Set the server and sender details used for application emails.</p>
                </div>
            </div>
        </div>

        <div class="space-y-6 px-5 py-6 sm:px-7">
            <div class="grid gap-5 md:grid-cols-6">
                <div class="md:col-span-3">
                    <label for="smtp-host" class="mb-1.5 block text-sm font-semibold text-slate-800">SMTP Host <span class="text-rose-600">*</span></label>
                    <input id="smtp-host" name="host" type="text" required maxlength="255" value="{{ old('host', $settings->host) }}" placeholder="smtp.example.com" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>
                <div class="md:col-span-1">
                    <label for="smtp-port" class="mb-1.5 block text-sm font-semibold text-slate-800">SMTP Port <span class="text-rose-600">*</span></label>
                    <input id="smtp-port" name="port" type="number" required min="1" max="65535" value="{{ old('port', $settings->port) }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>
                <div class="md:col-span-2">
                    <label for="smtp-encryption" class="mb-1.5 block text-sm font-semibold text-slate-800">Encryption <span class="text-rose-600">*</span></label>
                    <select id="smtp-encryption" name="encryption" required class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                        <option value="ssl" @selected(old('encryption', $settings->encryption) === 'ssl')>SSL/TLS (usually port 465)</option>
                        <option value="tls" @selected(old('encryption', $settings->encryption) === 'tls')>STARTTLS (usually port 587)</option>
                        <option value="none" @selected(old('encryption', $settings->encryption) === 'none')>None</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label for="smtp-username" class="mb-1.5 block text-sm font-semibold text-slate-800">SMTP Username</label>
                    <input id="smtp-username" name="username" type="text" maxlength="255" autocomplete="username" value="{{ old('username', $settings->username) }}" placeholder="Optional" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>
                <div class="md:col-span-3">
                    <label for="smtp-password" class="mb-1.5 block text-sm font-semibold text-slate-800">SMTP Password</label>
                    <input id="smtp-password" name="password" type="password" maxlength="500" autocomplete="new-password" placeholder="{{ $settings->exists && $settings->password ? 'Leave blank to keep current password' : 'Enter SMTP password if required' }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <p class="mt-1.5 text-xs text-slate-500">
                        @if ($settings->exists && $settings->password)
                            A password is saved and encrypted. Leave blank to keep it.
                        @else
                            Optional. The password is encrypted before it is stored.
                        @endif
                    </p>
                </div>

                <div class="md:col-span-3">
                    <label for="from-email" class="mb-1.5 block text-sm font-semibold text-slate-800">From Email <span class="text-rose-600">*</span></label>
                    <input id="from-email" name="from_email" type="email" required maxlength="255" value="{{ old('from_email', $settings->from_email) }}" placeholder="noreply@example.com" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>
                <div class="md:col-span-3">
                    <label for="from-name" class="mb-1.5 block text-sm font-semibold text-slate-800">From Name <span class="text-rose-600">*</span></label>
                    <input id="from-name" name="from_name" type="text" required maxlength="255" value="{{ old('from_name', $settings->from_name) }}" placeholder="{{ config('app.name') }}" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                </div>

                <div class="md:col-span-3">
                    <label for="test-recipient-email" class="mb-1.5 block text-sm font-semibold text-slate-800">Test Recipient Email</label>
                    <input id="test-recipient-email" name="test_recipient_email" type="email" maxlength="255" value="{{ old('test_recipient_email', $settings->test_recipient_email) }}" placeholder="you@example.com" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100">
                    <p class="mt-1.5 text-xs text-slate-500">Used when selecting “Save &amp; Send Test Email”.</p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <button type="submit" name="action" value="save" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Settings
                </button>
                <button type="submit" name="action" value="send_test" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                    <i class="fa-solid fa-paper-plane"></i>
                    Save &amp; Send Test Email
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
