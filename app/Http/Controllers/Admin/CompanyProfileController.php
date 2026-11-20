<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        $companyProfile = CompanyProfile::query()->firstOrCreate(
            ['id' => 1],
            ['company_name' => config('app.name', 'Tour & Travel')],
        );

        return view('admin.company-profile.edit', compact('companyProfile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $companyProfile = CompanyProfile::query()->firstOrCreate(
            ['id' => 1],
            ['company_name' => config('app.name', 'Tour & Travel')],
        );
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $oldLogoPath = $companyProfile->logo_path;
        $logoPath = $oldLogoPath;

        if (in_array($validated['remove_logo'] ?? false, [true, 1, '1'], true)) {
            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company', 'public');
        }

        $companyProfile->update([
            'company_name' => $validated['company_name'],
            'logo_path' => $logoPath,
        ]);

        if ($oldLogoPath !== null && $oldLogoPath !== $logoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return redirect()->route('admin.company-profile.edit')
            ->with('success', 'Company profile updated. The new branding is now visible across the admin dashboard.');
    }
}
