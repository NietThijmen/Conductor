<?php

namespace App\Http\Controllers\Api;

use App\Enums\ChangelogStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChangelogResource;
use App\Models\Changelog;
use App\Models\Package;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChangelogController extends Controller
{
    /**
     * List the checked changelogs (versions) for a package.
     */
    public function index(string $vendor, string $name): AnonymousResourceCollection
    {
        $packageName = $this->buildPackageName($vendor, $name);

        $package = Package::query()
            ->where('is_active', true)
            ->where('name', $packageName)
            ->firstOrFail();

        $changelogs = $package->changelogs()
            ->where('status', ChangelogStatus::Checked)
            ->latest()
            ->with('changes')
            ->paginate(15);

        return ChangelogResource::collection($changelogs);
    }

    /**
     * Show a single changelog and its change items for a package version.
     */
    public function show(string $vendor, string $name, string $new_version): ChangelogResource
    {
        $packageName = $this->buildPackageName($vendor, $name);

        $changelog = Changelog::query()
            ->whereHas('package', function ($query) use ($packageName): void {
                $query->where('is_active', true)->where('name', $packageName);
            })
            ->where('status', ChangelogStatus::Checked)
            ->where('new_version', $new_version)
            ->with('package', 'changes')
            ->firstOrFail();

        return new ChangelogResource($changelog);
    }

    /**
     * Combine the vendor and name route parameters into a full package name.
     */
    private function buildPackageName(?string $vendor, ?string $name): string
    {
        return trim(implode('/', array_filter([$vendor, $name])), '/');
    }
}
