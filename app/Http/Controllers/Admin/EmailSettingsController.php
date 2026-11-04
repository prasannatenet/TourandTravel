<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EmailSettingsTestMail;
use App\Models\EmailSetting;
use App\Services\EmailSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailSettingsController extends Controller
{
    public function edit(): View
    {
        $settings = EmailSetting::query()->find(1) ?? new EmailSetting([
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.scheme') === 'smtps'
                ? 'ssl'
                : ((int) config('mail.mailers.smtp.port') === 587 ? 'tls' : 'none'),
            'username' => config('mail.mailers.smtp.username'),
            'from_email' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ]);

        return view('admin.email-settings.edit', compact('settings'));
    }

    public function update(Request $request, EmailSettingsService $emailSettingsService): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => ['required', 'in:save,send_test'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['required', 'in:ssl,tls,none'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:500'],
            'from_email' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'test_recipient_email' => ['required_if:action,send_test', 'nullable', 'email', 'max:255'],
        ]);
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }
        $validated = $validator->validated();

        $settings = EmailSetting::query()->find(1) ?? new EmailSetting;
        $settings->fill([
            'host' => $validated['host'],
            'port' => (int) $validated['port'],
            'encryption' => $validated['encryption'],
            'username' => $validated['username'] ?? null,
            'from_email' => $validated['from_email'],
            'from_name' => $validated['from_name'],
            'test_recipient_email' => $validated['test_recipient_email'] ?? null,
        ]);

        if (filled($validated['password'] ?? null)) {
            $settings->password = $validated['password'];
        }

        $settings->id = 1;
        $settings->save();
        $emailSettingsService->apply($settings);
        Mail::purge('smtp');

        if ($validated['action'] === 'send_test') {
            try {
                Mail::mailer('smtp')
                    ->to($settings->test_recipient_email)
                    ->send(new EmailSettingsTestMail);
            } catch (TransportExceptionInterface $exception) {
                report($exception);

                return back()
                    ->withInput($request->except('password'))
                    ->with('error', 'Settings were saved, but the test email could not be sent. Check the SMTP host, port, encryption, and credentials.');
            }

            return redirect()->route('admin.email-settings.edit')
                ->with('success', 'Email settings were saved and the test email was sent successfully.');
        }

        return redirect()->route('admin.email-settings.edit')
            ->with('success', 'Email settings were saved successfully.');
    }
}
