<?php

namespace App\Console\Commands;

use App\Http\Integrations\Composer\Composer;
use App\Http\Integrations\Composer\Data\PackageMetadata;
use App\Http\Integrations\Composer\Data\PackageVersion;
use App\Http\Integrations\Composer\Requests\GetPackageMetadataRequest;
use App\Jobs\GenerateChangelog;
use App\Models\Package;
use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Throwable;
use UnexpectedValueException;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

#[Signature('package:check-for-updates {package? : The package name or ID} {--all : Check inactive packages too} {--include-unstable : Include non-stable releases} {--queue=default : The queue to dispatch jobs on} {--dry-run : Show what would be dispatched without dispatching}')]
#[Description('Poll Composer/Packagist for tracked packages and dispatch changelog generation jobs for missing updates')]
class CheckPackageUpdates extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $packages = $this->resolvePackages();

        if ($packages === null) {
            return self::FAILURE;
        }

        if ($packages->isEmpty()) {
            warning('No packages to check.');

            return self::SUCCESS;
        }

        $queue = $this->option('queue') ?: 'default';
        $dispatched = 0;
        $skipped = 0;
        $failures = 0;

        foreach ($packages as $package) {
            if ($package->current_version === null) {
                warning("Skipping [{$package->name}] because it has no current version.");
                $skipped++;

                continue;
            }

            $missing = $this->missingUpdateTransitionsFor($package);

            if ($missing === null) {
                $failures++;

                continue;
            }

            if ($missing->isEmpty()) {
                info("[{$package->name}] is up to date.");

                continue;
            }

            $lastVersion = $package->current_version;

            foreach ($missing as $transition) {
                /** @var array{old: string, new: string} $transition */
                if ($this->hasExistingChangelog($package, $transition['old'], $transition['new'])) {
                    $lastVersion = $transition['new'];

                    continue;
                }

                $wasDispatched = $this->handleMissingUpdate($package, $transition['old'], $transition['new'], $queue);

                if ($wasDispatched) {
                    $dispatched++;
                    $lastVersion = $transition['new'];
                } else {
                    $skipped++;

                    break;
                }
            }

            $this->updatePackageVersion($package, $lastVersion);
        }

        return $this->report($dispatched, $skipped, $failures);
    }

    /**
     * Resolve the packages to check based on the provided arguments and options.
     *
     * @return EloquentCollection<int, Package>|null
     */
    private function resolvePackages(): ?EloquentCollection
    {
        $identifier = $this->argument('package');

        if ($identifier !== null) {
            $package = is_numeric($identifier)
                ? Package::find((int) $identifier)
                : Package::where('name', $identifier)->first();

            if (! $package instanceof Package) {
                error('No package found with the given identifier.');

                return null;
            }

            return new EloquentCollection([$package]);
        }

        return Package::query()
            ->when(! $this->option('all'), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    /**
     * Fetch metadata from Composer/Packagist and determine which newer version
     * transitions do not have a changelog yet.
     *
     * @return Collection<int, array{old: string, new: string}>|null
     */
    private function missingUpdateTransitionsFor(Package $package): ?Collection
    {
        try {
            $connector = new Composer($package->registry);
            $response = $connector->send(new GetPackageMetadataRequest($package->name));
            /** @var PackageMetadata $metadata */
            $metadata = $response->dtoOrFail();
        } catch (RequestException|FatalRequestException $exception) {
            error("Failed to fetch metadata for [{$package->name}]: {$exception->getMessage()}");

            return null;
        }

        if ($metadata->versions === []) {
            warning("No versions found for [{$package->name}].");

            return Collection::make();
        }

        $baseline = $this->normalizeVersion($package->current_version);

        if ($baseline === null) {
            warning("Skipping [{$package->name}] because its current version [{$package->current_version}] is not a valid semver version.");

            return Collection::make();
        }

        $versions = Collection::make($metadata->versions)
            ->filter(fn (PackageVersion $version) => $this->isAcceptableVersion($version, $baseline))
            ->sortBy(fn (PackageVersion $version) => $this->normalizeVersion($version->version) ?? '')
            ->values();

        if ($versions->isEmpty()) {
            return Collection::make();
        }

        $transitions = Collection::make();
        $previousVersion = $package->current_version;

        foreach ($versions as $version) {
            $transitions->push([
                'old' => $previousVersion,
                'new' => $version->version,
            ]);

            $previousVersion = $version->version;
        }

        return $transitions;
    }

    /**
     * Determine if a remote version is acceptable for changelog chain building.
     */
    private function isAcceptableVersion(PackageVersion $version, string $baseline): bool
    {
        if (! $this->option('include-unstable') && ! $this->isStable($version->version)) {
            return false;
        }

        $normalized = $this->normalizeVersion($version->version);

        if ($normalized === null) {
            return false;
        }

        return Comparator::greaterThan($normalized, $baseline);
    }

    /**
     * Dispatch a changelog generation job for a missing update, or report it in dry-run mode.
     */
    private function handleMissingUpdate(Package $package, string $oldVersion, string $newVersion, string $queue): bool
    {
        if ($this->option('dry-run')) {
            info("[{$package->name}] missing changelog [{$oldVersion} -> {$newVersion}] (dry-run).");

            return true;
        }

        try {
            GenerateChangelog::dispatch($package, $oldVersion, $newVersion)
                ->onQueue($queue);

            info("Dispatched changelog generation for [{$package->name}] from [{$oldVersion}] to [{$newVersion}].");

            return true;
        } catch (Throwable $exception) {
            error("Failed to dispatch changelog for [{$package->name}]: {$exception->getMessage()}");

            return false;
        }
    }

    /**
     * Determine if a changelog already exists for the given version transition.
     */
    private function hasExistingChangelog(Package $package, string $oldVersion, string $newVersion): bool
    {
        return $package->changelogs()
            ->where('old_version', $oldVersion)
            ->where('new_version', $newVersion)
            ->exists();
    }

    /**
     * Update the tracked package version to the latest processed version.
     */
    private function updatePackageVersion(Package $package, string $version): void
    {
        if ($this->option('dry-run')) {
            info("[{$package->name}] would update current version to [{$version}] (dry-run).");

            return;
        }

        $package->update(['current_version' => $version]);
    }

    /**
     * Check whether a version is stable.
     */
    private function isStable(string $version): bool
    {
        return VersionParser::parseStability($version) === 'stable';
    }

    /**
     * Normalize a version string to a comparator-safe value.
     */
    private function normalizeVersion(string $version): ?string
    {
        try {
            return (new VersionParser)->normalize($version);
        } catch (UnexpectedValueException) {
            return null;
        }
    }

    /**
     * Report the result of the command and return the appropriate exit code.
     */
    private function report(int $dispatched, int $skipped, int $failures): int
    {
        if ($failures > 0) {
            error("Completed with {$failures} failure(s). {$dispatched} job(s) dispatched, {$skipped} skipped.");

            return self::FAILURE;
        }

        info("Completed. {$dispatched} job(s) dispatched, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
