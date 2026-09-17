<?php

namespace App\Console\Commands;

use App\Enums\ChangelogStatus;
use App\Models\Package;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

#[Description('View changelogs for a package')]
#[Signature('package:changelog {package? : The package name or ID} {--status=} {--limit=50}')]
class ShowPackageChangelog extends Command
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

        $status = $this->option('status');
        if ($status !== null) {
            $error = $this->validateStatus($status);
            if ($error !== null) {
                error($error);

                return self::FAILURE;
            }
        }

        $changelogs = $package->changelogs()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->limit((int) $this->option('limit'))
            ->with('changes')
            ->get();

        if ($changelogs->isEmpty()) {
            info("No changelogs found for package [{$package->name}].");

            return self::SUCCESS;
        }

        info("Changelogs for package [{$package->name}]:");

        foreach ($changelogs as $changelog) {
            table(
                headers: ['Field', 'Value'],
                rows: [
                    ['ID', (string) $changelog->id],
                    ['Old Version', $changelog->old_version],
                    ['New Version', $changelog->new_version],
                    ['Status', $changelog->status->value],
                    ['Title', $changelog->title ?? 'N/A'],
                    ['Summary', $changelog->summary ?? 'N/A'],
                    ['Changes', (string) $changelog->changes->count()],
                    ['Created At', $changelog->created_at?->toDateTimeString() ?? ''],
                ],
            );

            if ($changelog->changes->isNotEmpty()) {
                table(
                    headers: ['Type', 'Title', 'Message'],
                    rows: $changelog->changes->map(fn ($change) => [
                        $change->type->value,
                        $change->title,
                        $change->message ?? 'N/A',
                    ])->toArray(),
                );
            }

            $this->newLine();
        }

        return self::SUCCESS;
    }

    /**
     * Resolve a package from the provided options or prompt.
     */
    private function resolvePackage(): ?Package
    {
        $identifier = $this->argument('package');

        if ($identifier !== null) {
            if (is_numeric($identifier)) {
                $package = Package::find((int) $identifier);
            } else {
                $package = Package::where('name', $identifier)->first();
            }

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
     * Validate the status option.
     */
    private function validateStatus(string $status): ?string
    {
        $allowed = collect(ChangelogStatus::cases())
            ->map(fn (ChangelogStatus $case) => $case->value)
            ->implode(', ');

        if (! in_array($status, explode(', ', $allowed), true)) {
            return "The status must be one of: {$allowed}.";
        }

        return null;
    }
}
