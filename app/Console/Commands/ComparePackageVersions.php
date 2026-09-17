<?php

namespace App\Console\Commands;

use App\Jobs\GenerateChangelog;
use App\Models\Package;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

#[Signature('package:compare {package? : The package name or ID} {old-version?} {new-version?}')]
#[Description('Generate a changelog comparing two versions of a tracked Composer package')]
class ComparePackageVersions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $package = $this->resolvePackage();

        if (! $package instanceof Package) {
            return self::FAILURE;
        }

        $oldVersion = $this->resolveVersion('old-version');
        $newVersion = $this->resolveVersion('new-version');

        GenerateChangelog::dispatch($package, $oldVersion, $newVersion);

        info("Changelog generation dispatched for [{$package->name}] from [{$oldVersion}] to [{$newVersion}].");

        return self::SUCCESS;
    }

    /**
     * Resolve a tracked package from the provided options or prompt.
     */
    private function resolvePackage(): ?Package
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

            return $package;
        }

        $name = text(
            label: 'What is the package\'s name?',
            required: true,
        );

        $package = Package::where('name', $name)->first();

        if (! $package instanceof Package) {
            error('No package found with the given name.');

            return null;
        }

        return $package;
    }

    /**
     * Resolve a version argument, falling back to prompt when not provided.
     */
    private function resolveVersion(string $argument): string
    {
        $value = $this->argument($argument);

        if ($value !== null && $value !== '' && is_string($value)) {
            return $value;
        }

        return text(
            label: "What is the {$argument}?",
            required: true,
        );
    }
}
