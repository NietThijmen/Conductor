<?php

namespace App\Console\Commands;

use App\Enums\RegistryAuthType;
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

#[Description('Manage package registries')]
#[Signature('registry:manage {action : The action to perform (list, create, update, delete, show)} {--name=} {--url=} {--auth-type=} {--auth-username=} {--auth-password=} {--auth-token=} {--active=} {--id=} {--force}')]
class ManageRegistry extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listRegistries(),
            'create' => $this->createRegistry(),
            'update' => $this->updateRegistry(),
            'delete' => $this->deleteRegistry(),
            'show' => $this->showRegistry(),
            default => $this->invalidAction($action),
        };
    }

    /**
     * List all registries.
     */
    private function listRegistries(): int
    {
        $registries = Registry::query()
            ->orderBy('name')
            ->get(['id', 'name', 'url', 'auth_type', 'is_active', 'created_at']);

        if ($registries->isEmpty()) {
            info('No registries found.');

            return self::SUCCESS;
        }

        table(
            headers: ['ID', 'Name', 'URL', 'Auth Type', 'Active', 'Created At'],
            rows: $registries->map(fn (Registry $registry) => [
                (string) $registry->id,
                $registry->name,
                $registry->url,
                $registry->auth_type->value,
                $registry->is_active ? 'Yes' : 'No',
                $registry->created_at?->toDateTimeString() ?? '',
            ])->toArray(),
        );

        return self::SUCCESS;
    }

    /**
     * Create a new registry.
     */
    private function createRegistry(): int
    {
        $name = $this->option('name') ?? text(
            label: 'What is the registry\'s name?',
            required: true,
            validate: fn (string $value) => $this->validateRegistryName($value),
        );

        $url = $this->option('url') ?? text(
            label: 'What is the registry\'s URL?',
            required: true,
            validate: fn (string $value) => $this->validateRegistryUrl($value),
        );

        $authType = $this->option('auth-type') ?? select(
            label: 'What authentication type does the registry use?',
            options: $this->authTypeOptions(),
            default: RegistryAuthType::None->value,
        );

        $authConfig = $this->buildAuthConfig(RegistryAuthType::from($authType));
        $isActive = $this->parseBooleanOption($this->option('active')) ?? confirm(
            label: 'Is the registry active?',
            default: true,
        );

        $registry = Registry::create([
            'name' => $name,
            'url' => $url,
            'auth_type' => $authType,
            'auth_config' => $authConfig,
            'is_active' => $isActive,
        ]);

        info("Registry [{$registry->name}] created successfully.");

        return self::SUCCESS;
    }

    /**
     * Update an existing registry.
     */
    private function updateRegistry(): int
    {
        $registry = $this->resolveRegistry();

        if (! $registry instanceof Registry) {
            return self::FAILURE;
        }

        $name = $this->option('name') ?? $registry->name;
        $url = $this->option('url') ?? $registry->url;
        $authType = $this->option('auth-type') ?? $registry->auth_type->value;

        $nameError = $this->validateRegistryName($name, $registry->id);
        if ($nameError !== null) {
            error($nameError);

            return self::FAILURE;
        }

        $urlError = $this->validateRegistryUrl($url, $registry->id);
        if ($urlError !== null) {
            error($urlError);

            return self::FAILURE;
        }

        $authConfig = $this->resolveAuthConfigForUpdate($registry, $authType);
        $isActive = $this->parseBooleanOption($this->option('active')) ?? $registry->is_active;

        $registry->update([
            'name' => $name,
            'url' => $url,
            'auth_type' => $authType,
            'auth_config' => $authConfig,
            'is_active' => $isActive,
        ]);

        info("Registry [{$registry->name}] updated successfully.");

        return self::SUCCESS;
    }

    /**
     * Delete a registry.
     */
    private function deleteRegistry(): int
    {
        $registry = $this->resolveRegistry();

        if (! $registry instanceof Registry) {
            return self::FAILURE;
        }

        if ($registry->packages()->exists()) {
            error('Cannot delete a registry that still has packages assigned to it.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to delete registry [{$registry->name}]?")) {
            info('Deletion cancelled.');

            return self::SUCCESS;
        }

        $registry->delete();

        info("Registry [{$registry->name}] deleted successfully.");

        return self::SUCCESS;
    }

    /**
     * Show a single registry's details.
     */
    private function showRegistry(): int
    {
        $registry = $this->resolveRegistry();

        if (! $registry instanceof Registry) {
            return self::FAILURE;
        }

        table(
            headers: ['Attribute', 'Value'],
            rows: [
                ['ID', (string) $registry->id],
                ['Name', $registry->name],
                ['URL', $registry->url],
                ['Auth Type', $registry->auth_type->value],
                ['Active', $registry->is_active ? 'Yes' : 'No'],
                ['Packages', (string) $registry->packages()->count()],
                ['Created At', $registry->created_at?->toDateTimeString() ?? ''],
                ['Updated At', $registry->updated_at?->toDateTimeString() ?? ''],
            ],
        );

        return self::SUCCESS;
    }

    /**
     * Resolve a registry from the provided options or prompt.
     */
    private function resolveRegistry(): ?Registry
    {
        if ($this->option('id')) {
            $registry = Registry::find($this->option('id'));

            if (! $registry instanceof Registry) {
                error('No registry found with the given ID.');

                return null;
            }

            return $registry;
        }

        $name = $this->option('name') ?? text(
            label: 'What is the registry\'s name?',
            required: true,
        );

        $registry = Registry::where('name', $name)->first();

        if (! $registry instanceof Registry) {
            error('No registry found with the given name.');

            return null;
        }

        return $registry;
    }

    /**
     * Resolve the authentication config for a registry update.
     *
     * @return array<string, mixed>|null
     */
    private function resolveAuthConfigForUpdate(Registry $registry, string $authType): ?array
    {
        if ($authType !== $registry->auth_type->value) {
            return $this->buildAuthConfig(RegistryAuthType::from($authType));
        }

        if ($this->option('auth-username') || $this->option('auth-password') || $this->option('auth-token')) {
            return $this->buildAuthConfig(
                RegistryAuthType::from($authType),
                $registry->auth_config ?? [],
            );
        }

        return $registry->auth_config;
    }

    /**
     * Build the authentication configuration for the given auth type.
     *
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>|null
     */
    private function buildAuthConfig(RegistryAuthType $authType, array $existing = []): ?array
    {
        return match ($authType) {
            RegistryAuthType::None => null,
            RegistryAuthType::Basic => [
                'username' => $this->option('auth-username') ?? text(
                    label: 'Username',
                    default: $existing['username'] ?? '',
                    required: true,
                ),
                'password' => $this->option('auth-password') ?? text(
                    label: 'Password',
                    default: $existing['password'] ?? '',
                    required: true,
                ),
            ],
            RegistryAuthType::Token => [
                'token' => $this->option('auth-token') ?? text(
                    label: 'Token',
                    default: $existing['token'] ?? '',
                    required: true,
                ),
            ],
            RegistryAuthType::Composer => [
                'username' => $this->option('auth-username') ?? text(
                    label: 'Composer username',
                    default: $existing['username'] ?? '',
                    required: true,
                ),
                'password' => $this->option('auth-password') ?? text(
                    label: 'Composer password / API token',
                    default: $existing['password'] ?? '',
                    required: true,
                ),
            ],
        };
    }

    /**
     * Get the authentication type options for a select prompt.
     *
     * @return array<string, string>
     */
    private function authTypeOptions(): array
    {
        return collect(RegistryAuthType::cases())
            ->mapWithKeys(fn (RegistryAuthType $type) => [$type->value => ucfirst($type->value)])
            ->toArray();
    }

    /**
     * Validate a registry name.
     */
    private function validateRegistryName(string $value, ?int $excludeId = null): ?string
    {
        try {
            validator(
                data: ['name' => $value],
                rules: ['name' => ['required', 'string', 'max:255', Rule::unique(Registry::class, 'name')->ignore($excludeId)]],
            )->validate();
        } catch (ValidationException $exception) {
            return $exception->getMessage();
        }

        return null;
    }

    /**
     * Validate a registry URL.
     */
    private function validateRegistryUrl(string $value, ?int $excludeId = null): ?string
    {
        try {
            validator(
                data: ['url' => $value],
                rules: ['url' => ['required', 'url', Rule::unique(Registry::class, 'url')->ignore($excludeId)]],
            )->validate();
        } catch (ValidationException $exception) {
            return $exception->getMessage();
        }

        return null;
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
