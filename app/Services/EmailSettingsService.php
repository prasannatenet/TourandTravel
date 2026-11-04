<?php

namespace App\Services;

use App\Models\EmailSetting;

class EmailSettingsService
{
    public function apply(EmailSetting $settings): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                ...config('mail.mailers.smtp'),
                'transport' => 'smtp',
                'scheme' => $settings->encryption === 'ssl' ? 'smtps' : 'smtp',
                'url' => null,
                'host' => $settings->host,
                'port' => $settings->port,
                'username' => $settings->username,
                'password' => $settings->password,
                'auto_tls' => $settings->encryption === 'tls',
            ],
            'mail.from.address' => $settings->from_email,
            'mail.from.name' => $settings->from_name,
        ]);
    }
}
