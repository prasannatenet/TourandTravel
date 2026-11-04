<?php

namespace App\Providers;

use App\Models\EmailSetting;
use App\Services\EmailSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('email_settings')) {
            $settings = EmailSetting::query()->find(1);

            if ($settings !== null) {
                app(EmailSettingsService::class)->apply($settings);
            }
        }
    }
}
