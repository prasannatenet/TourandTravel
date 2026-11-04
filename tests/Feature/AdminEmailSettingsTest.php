<?php

namespace Tests\Feature;

use App\Mail\EmailSettingsTestMail;
use App\Models\EmailSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminEmailSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_email_settings(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.email-settings.edit'))
            ->assertOk()
            ->assertSee('SMTP Configuration')
            ->assertSee('SMTP Host')
            ->assertSee('SMTP Port')
            ->assertSee('SMTP Username')
            ->assertSee('SMTP Password')
            ->assertSee('From Email')
            ->assertSee('From Name')
            ->assertSee('Test Recipient Email')
            ->assertSee('Save & Send Test Email');
    }

    public function test_admin_can_save_settings_and_send_a_test_email(): void
    {
        Mail::fake();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.email-settings.update'), [
                'action' => 'send_test',
                'host' => 'smtp.example.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'mailer-user',
                'password' => 'smtp-secret',
                'from_email' => 'noreply@example.com',
                'from_name' => 'Tour and Travel',
                'test_recipient_email' => 'admin@example.com',
            ])
            ->assertRedirect(route('admin.email-settings.edit'))
            ->assertSessionHas('success', 'Email settings were saved and the test email was sent successfully.');

        $settings = EmailSetting::query()->findOrFail(1);
        $this->assertSame('smtp-secret', $settings->password);
        $this->assertArrayNotHasKey('password', $settings->toArray());
        $this->assertNotSame('smtp-secret', DB::table('email_settings')->where('id', 1)->value('password'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('smtp', config('mail.mailers.smtp.scheme'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));

        Mail::assertSent(EmailSettingsTestMail::class, fn (EmailSettingsTestMail $mail): bool => $mail->hasTo('admin@example.com'));
    }

    public function test_saving_without_a_password_keeps_the_existing_encrypted_password(): void
    {
        $settings = new EmailSetting([
            'host' => 'smtp.example.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'mailer-user',
            'password' => 'existing-secret',
            'from_email' => 'noreply@example.com',
            'from_name' => 'Tour and Travel',
        ]);
        $settings->id = 1;
        $settings->save();

        $this->actingAs($this->admin())
            ->put(route('admin.email-settings.update'), [
                'action' => 'save',
                'host' => 'smtp.updated.example.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username' => 'mailer-user',
                'password' => '',
                'from_email' => 'noreply@example.com',
                'from_name' => 'Tour and Travel',
            ])
            ->assertRedirect(route('admin.email-settings.edit'));

        $settings = EmailSetting::query()->findOrFail(1);
        $this->assertSame('existing-secret', $settings->password);
        $this->assertSame('smtp.updated.example.com', $settings->host);
    }

    public function test_guest_cannot_access_email_settings(): void
    {
        $this->get(route('admin.email-settings.edit'))
            ->assertRedirect(route('admin.login'));

        $this->put(route('admin.email-settings.update'), [
            'action' => 'save',
        ])->assertRedirect(route('admin.login'));
    }

    public function test_email_settings_reject_an_invalid_encryption_option(): void
    {
        $this->from(route('admin.email-settings.edit'))
            ->actingAs($this->admin())
            ->put(route('admin.email-settings.update'), [
                'action' => 'save',
                'host' => 'smtp.example.com',
                'port' => 587,
                'encryption' => 'invalid',
                'username' => 'mailer-user',
                'from_email' => 'noreply@example.com',
                'from_name' => 'Tour and Travel',
            ])
            ->assertRedirect(route('admin.email-settings.edit'))
            ->assertSessionHasErrors('encryption');
    }

    public function test_invalid_settings_do_not_flash_the_smtp_password_to_the_session(): void
    {
        $this->from(route('admin.email-settings.edit'))
            ->actingAs($this->admin())
            ->put(route('admin.email-settings.update'), [
                'action' => 'save',
                'host' => 'smtp.example.com',
                'port' => 587,
                'encryption' => 'tls',
                'password' => 'secret-that-must-not-be-flashed',
                'from_email' => 'not-an-email',
                'from_name' => 'Tour and Travel',
            ])
            ->assertRedirect(route('admin.email-settings.edit'))
            ->assertSessionHasErrors('from_email')
            ->assertSessionMissing('_old_input.password');
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }
}
