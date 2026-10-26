<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageInclusion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PackageInclusionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $inclusions = PackageInclusion::withCount('packages')->orderBy('name')->paginate(15);

        return view('admin.inclusions.index', compact('inclusions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.inclusions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        foreach (['image_one', 'image_two'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $validated[$imageField] = $request->file($imageField)->store('inclusions', 'public');
            } else {
                unset($validated[$imageField]);
            }
        }
        unset($validated['remove_image_one'], $validated['remove_image_two']);

        PackageInclusion::create($validated);

        return redirect()->route('admin.inclusions.index')->with('success', 'Package inclusion created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PackageInclusion $inclusion): View
    {
        return view('admin.inclusions.edit', compact('inclusion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PackageInclusion $inclusion): RedirectResponse
    {
        $validated = $this->validatedData($request, $inclusion);

        foreach (['image_one', 'image_two'] as $imageField) {
            $removeImageField = 'remove_'.$imageField;

            if ($request->hasFile($imageField)) {
                if ($inclusion->{$imageField}) {
                    Storage::disk('public')->delete($inclusion->{$imageField});
                }

                $validated[$imageField] = $request->file($imageField)->store('inclusions', 'public');
            } elseif ($request->boolean($removeImageField)) {
                if ($inclusion->{$imageField}) {
                    Storage::disk('public')->delete($inclusion->{$imageField});
                }

                $validated[$imageField] = null;
            } else {
                unset($validated[$imageField]);
            }
        }
        unset($validated['remove_image_one'], $validated['remove_image_two']);

        $inclusion->update($validated);

        return redirect()->route('admin.inclusions.index')->with('success', 'Package inclusion updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PackageInclusion $inclusion): RedirectResponse
    {
        foreach (['image_one', 'image_two'] as $imageField) {
            if ($inclusion->{$imageField}) {
                Storage::disk('public')->delete($inclusion->{$imageField});
            }
        }

        $inclusion->delete();

        return redirect()->route('admin.inclusions.index')->with('success', 'Package inclusion deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?PackageInclusion $inclusion = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:package_inclusions,name'.($inclusion ? ','.$inclusion->id : '')],
            'description' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && preg_match_all('/\\S+/u', trim($value)) > 200) {
                    $fail('The description may not exceed 200 words.');
                }
            }],
            'image_one' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_two' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image_one' => ['sometimes', 'boolean'],
            'remove_image_two' => ['sometimes', 'boolean'],
        ]);
    }
}
