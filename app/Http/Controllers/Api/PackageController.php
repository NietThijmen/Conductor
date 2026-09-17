<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChangelogStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PackageController extends Controller
{
    /**
     * List active packages with optional name and vendor filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'vendor' => ['nullable', 'string', 'max:255'],
        ]);

        $packages = Package::query()
            ->where('is_active', true)
            ->when($data['vendor'] ?? null, function ($query, string $vendor): void {
                $escaped = str_replace('\\', '\\\\', $vendor);
                $query->where('name', 'like', $escaped.'/%');
            })
            ->when($data['q'] ?? null, function ($query, string $search): void {
                $term = '%'.str_replace('\\', '\\\\', $search).'%';
                $query->where('name', 'like', $term);
            })
            ->withCount('changelogs')
            ->with('latestChangelog')
            ->orderBy('name')
            ->paginate(15);

        return PackageResource::collection($packages);
    }

    /**
     * Show a single package and its checked changelogs.
     */
    public function show(Request $request, string $vendor, string $name): PackageResource
    {
        $packageName = $this->buildPackageName($vendor, $name);

        $package = Package::query()
            ->where('is_active', true)
            ->where('name', $packageName)
            ->withCount('changelogs')
            ->with(['changelogs' => function ($query): void {
                $query->where('status', ChangelogStatus::Checked)
                    ->latest()
                    ->with('changes');
            }])
            ->firstOrFail();

        return new PackageResource($package);
    }

    /**
     * Combine the vendor and name route parameters into a full package name.
     */
    private function buildPackageName(?string $vendor, ?string $name): string
    {
        return trim(implode('/', array_filter([$vendor, $name])), '/');
    }
}
