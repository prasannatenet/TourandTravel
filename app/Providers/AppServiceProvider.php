<?php

namespace App\Providers;

use App\Models\CompanyProfile;
use App\Models\EmailSetting;
use App\Services\EmailSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.admin', function ($view): void {
            $companyProfile = Schema::hasTable('company_profiles')
                ? CompanyProfile::query()->find(1)
                : null;

            $view->with('companyProfile', $companyProfile);
        });

        if (Schema::hasTable('email_settings')) {
            $settings = EmailSetting::query()->find(1);

            if ($settings !== null) {
                app(EmailSettingsService::class)->apply($settings);
            }
        }
    }
}
