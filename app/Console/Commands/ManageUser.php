<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

#[Description('Manage application users')]
#[Signature('user:manage {action : The action to perform (list, create, delete, show)} {--name=} {--email=} {--password=} {--id=} {--force}')]
class ManageUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'list' => $this->listUsers(),
            'create' => $this->createUser(),
            'delete' => $this->deleteUser(),
            'show' => $this->showUser(),
            default => $this->invalidAction($action),
        };
    }

    /**
     * List all users.
     */
    private function listUsers(): int
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'created_at']);

        if ($users->isEmpty()) {
            info('No users found.');

            return self::SUCCESS;
        }

        table(
            headers: ['ID', 'Name', 'Email', 'Created At'],
            rows: $users->map(fn (User $user) => [
                (string) $user->id,
                $user->name,
                $user->email,
                $user->created_at?->toDateTimeString() ?? '',
            ])->toArray(),
        );

        return self::SUCCESS;
    }

    /**
     * Create a new user.
     */
    private function createUser(): int
    {
        $name = $this->option('name') ?? text(
            label: 'What is the user\'s name?',
            required: true,
        );

        $email = $this->option('email') ?? text(
            label: 'What is the user\'s email address?',
            required: true,
            validate: fn (string $value) => $this->validateEmail($value),
        );

        $password = $this->option('password') ?? password(
            label: 'What is the user\'s password?',
            required: true,
            validate: fn (string $value) => match (true) {
                strlen($value) < 8 => 'The password must be at least 8 characters.',
                default => null,
            },
        );

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        info("User [{$user->name}] created successfully.");

        return self::SUCCESS;
    }

    /**
     * Delete a user.
     */
    private function deleteUser(): int
    {
        $user = $this->resolveUser();

        if (! $user instanceof User) {
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to delete user [{$user->name}]?")) {
            info('Deletion cancelled.');

            return self::SUCCESS;
        }

        $user->delete();

        info("User [{$user->name}] deleted successfully.");

        return self::SUCCESS;
    }

    /**
     * Show a single user's details.
     */
    private function showUser(): int
    {
        $user = $this->resolveUser();

        if (! $user instanceof User) {
            return self::FAILURE;
        }

        table(
            headers: ['Attribute', 'Value'],
            rows: [
                ['ID', (string) $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Email Verified At', $user->email_verified_at?->toDateTimeString() ?? 'Not verified'],
                ['Created At', $user->created_at?->toDateTimeString() ?? ''],
                ['Updated At', $user->updated_at?->toDateTimeString() ?? ''],
            ],
        );

        return self::SUCCESS;
    }

    /**
     * Resolve a user from the provided options or prompt.
     */
    private function resolveUser(): ?User
    {
        if ($this->option('id')) {
            $user = User::find($this->option('id'));

            if (! $user instanceof User) {
                error('No user found with the given ID.');

                return null;
            }

            return $user;
        }

        $email = $this->option('email') ?? text(
            label: 'What is the user\'s email address?',
            required: true,
        );

        $user = User::where('email', $email)->first();

        if (! $user instanceof User) {
            error('No user found with the given email address.');

            return null;
        }

        return $user;
    }

    /**
     * Validate an email address for a new user.
     */
    private function validateEmail(string $email): ?string
    {
        try {
            validator(
                data: ['email' => $email],
                rules: ['email' => ['required', 'email', Rule::unique(User::class, 'email')]],
            )->validate();
        } catch (ValidationException $exception) {
            return $exception->getMessage();
        }

        return null;
    }

    /**
     * Report an invalid action and return a failure code.
     */
    private function invalidAction(string $action): int
    {
        error("Invalid action [{$action}]. Allowed actions: list, create, delete, show.");

        return self::FAILURE;
    }
}
