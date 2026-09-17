<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Registry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

#[Description('Manage tracked packages')]
#[Signature('package:manage {action : The action to perform (list, create, update, delete, show)} {--name=} {--registry-id=} {--registry=} {--current-version=} {--active=} {--id=} {--force}')]
class ManagePackage extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listPackages(),
            'create' => $this->createPackage(),
            'update' => $this->updatePackage(),
            'delete' => $this->deletePackage(),
            'show' => $this->showPackage(),
            default => $this->invalidAction($action),
        };
    }

    /**
     * List all packages.
     */
    private function listPackages(): int
    {
        $packages = Package::query()
            ->with('registry')
            ->orderBy('name')
            ->get(['id', 'registry_id', 'name', 'current_version', 'is_active', 'created_at']);

        if ($packages->isEmpty()) {
            info('No packages found.');

            return self::SUCCESS;
        }

        table(
            headers: ['ID', 'Name', 'Registry', 'Current Version', 'Active', 'Created At'],
            rows: $packages->map(fn (Package $package) => [
                (string) $package->id,
                $package->name,
                $package->registry !== null ? $package->registry->name : 'None',
                $package->current_version ?? 'N/A',
                $package->is_active ? 'Yes' : 'No',
                $package->created_at?->toDateTimeString() ?? '',
            ])->toArray(),
        );

        return self::SUCCESS;
    }

    /**
     * Create a new package.
     */
    private function createPackage(): int
    {
        $name = $this->option('name') ?? text(
            label: 'What is the package\'s name?',
            required: true,
            validate: fn (string $value) => $this->validatePackageName($value),
        );

        $registryId = $this->resolveRegistryId();

        $currentVersion = $this->option('current-version') ?? text(
            label: 'What is the package\'s current version?',
            default: '',
        );

        $isActive = $this->parseBooleanOption($this->option('active')) ?? confirm(
            label: 'Is the package active?',
            default: true,
        );

        $package = Package::create([
            'name' => $name,
            'registry_id' => $registryId,
            'current_version' => $currentVersion ?: null,
            'is_active' => $isActive,
        ]);

        info("Package [{$package->name}] created successfully.");

        return self::SUCCESS;
    }

    /**
     * Update an existing package.
     */
    private function updatePackage(): int
    {
        $package = $this->resolvePackage();

        if (! $package instanceof Package) {
            return self::FAILURE;
        }

        $name = $this->option('name') ?? $package->name;
        $registryId = $this->resolveRegistryIdForUpdate($package);
        $currentVersion = $this->option('current-version') ?? $package->current_version ?? '';
        $isActive = $this->parseBooleanOption($this->option('active')) ?? $package->is_active;

        $nameError = $this->validatePackageName($name, $package->id);
        if ($nameError !== null) {
            error($nameError);

            return self::FAILURE;
        }

        $package->update([
            'name' => $name,
            'registry_id' => $registryId,
            'current_version' => $currentVersion ?: null,
            'is_active' => $isActive,
        ]);

        info("Package [{$package->name}] updated successfully.");

        return self::SUCCESS;
    }

    /**
     * Delete a package.
     */
    private function deletePackage(): int
    {
        $package = $this->resolvePackage();

        if (! $package instanceof Package) {
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to delete package [{$package->name}]?")) {
            info('Deletion cancelled.');

            return self::SUCCESS;
        }

        $package->delete();

        info("Package [{$package->name}] deleted successfully.");

        return self::SUCCESS;
    }

    /**
     * Show a single package's details.
     */
    private function showPackage(): int
    {
        $package = $this->resolvePackage();

        if (! $package instanceof Package) {
            return self::FAILURE;
        }

        table(
            headers: ['Attribute', 'Value'],
            rows: [
                ['ID', (string) $package->id],
                ['Name', $package->name],
                ['Registry', $package->registry !== null ? $package->registry->name : 'None'],
                ['Current Version', $package->current_version ?? 'N/A'],
                ['Active', $package->is_active ? 'Yes' : 'No'],
                ['Changelogs', (string) $package->changelogs()->count()],
                ['Created At', $package->created_at?->toDateTimeString() ?? ''],
                ['Updated At', $package->updated_at?->toDateTimeString() ?? ''],
            ],
        );

        return self::SUCCESS;
    }

    /**
     * Resolve a package from the provided options or prompt.
     */
    private function resolvePackage(): ?Package
    {
        if ($this->option('id')) {
            $package = Package::find($this->option('id'));

            if (! $package instanceof Package) {
                error('No package found with the given ID.');

                return null;
            }

            return $package;
        }

        $name = $this->option('name') ?? text(
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
     * Resolve the registry ID for a package update.
     */
    private function resolveRegistryIdForUpdate(Package $package): ?int
    {
        if ($this->option('registry-id')) {
            return $this->toInt($this->option('registry-id'));
        }

        if ($this->option('registry')) {
            $registry = Registry::where('name', $this->option('registry'))->first();

            return $registry?->id;
        }

        return $package->registry_id;
    }

    /**
     * Resolve the registry ID from options or prompt.
     */
    private function resolveRegistryId(?int $default = null): ?int
    {
        if ($this->option('registry-id')) {
            return $this->toInt($this->option('registry-id'));
        }

        if ($this->option('registry')) {
            $registry = Registry::where('name', $this->option('registry'))->first();

            return $registry?->id;
        }

        $registries = Registry::orderBy('name')->pluck('name', 'id');

        if ($registries->isEmpty()) {
            return null;
        }

        $options = $registries
            ->map(fn (string $name) => $name)
            ->prepend('No registry', '')
            ->toArray();

        $selected = select(
            label: 'Which registry does the package belong to?',
            options: $options,
            default: (string) ($default ?? ''),
        );

        return $selected !== '' ? $this->toInt($selected) : null;
    }

    /**
     * Validate a package name.
     */
    private function validatePackageName(string $value, ?int $excludeId = null): ?string
    {
        try {
            validator(
                data: ['name' => $value],
                rules: ['name' => ['required', 'string', 'max:255', Rule::unique(Package::class, 'name')->ignore($excludeId)]],
            )->validate();
        } catch (ValidationException $exception) {
            return $exception->getMessage();
        }

        return null;
    }

    /**
     * Convert a value to an integer or null.
     */
    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Parse a boolean option value.
     */
    private function parseBooleanOption(?string $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        return in_array(strtolower($value), ['yes', 'y', 'true', '1'], true);
    }

    /**
     * Report an invalid action and return a failure code.
     */
    private function invalidAction(string $action): int
    {
        error("Invalid action [{$action}]. Allowed actions: list, create, update, delete, show.");

        return self::FAILURE;
    }
}
