<?php

namespace App\Jobs;

use App\Ai\Agents\ChangelogGenerator;
use App\Enums\ChangelogChangeType;
use App\Enums\ChangelogStatus;
use App\Models\Changelog;
use App\Models\ChangelogChange;
use App\Models\Package;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use NietThijmen\ComposerChangelog\Contracts\FileSystemDiffer;
use NietThijmen\ComposerChangelog\Contracts\PackageDownloader;
use NietThijmen\ComposerChangelog\Data\DirectoryDiff;
use NietThijmen\ComposerChangelog\Enums\FileChangeStatus;
use RuntimeException;
use Throwable;

class GenerateChangelog implements ShouldQueue
{
    use Queueable;

    public readonly Changelog $changelog;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Package $package,
        public readonly string $oldVersion,
        public readonly string $newVersion,
    ) {
        $this->changelog = Changelog::create([
            'package_id' => $this->package->id,
            'old_version' => $this->oldVersion,
            'new_version' => $this->newVersion,
            'status' => ChangelogStatus::Backlog,
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(
        PackageDownloader $downloader,
        FileSystemDiffer $differ,
        ChangelogGenerator $agent,
    ): void {
        $this->changelog->update(['status' => ChangelogStatus::Checking]);

        $basePath = null;

        try {
            $basePath = Storage::disk('local')->path('package-versions/'.Str::uuid());

            $oldDirectory = $downloader->download($this->package->name, $this->oldVersion, $basePath);
            $newDirectory = $downloader->download($this->package->name, $this->newVersion, $basePath);

            $diff = $differ->compare($oldDirectory, $newDirectory);
            $response = $agent->prompt($this->buildPrompt($diff));

            if (! $response instanceof StructuredAgentResponse) {
                throw new RuntimeException('Expected a structured response from the changelog generator agent.');
            }

            DB::transaction(function () use ($response): void {
                $this->persistGeneratedChanges($response);

                $this->changelog->update([
                    'status' => ChangelogStatus::Checked,
                    'title' => $response['title'],
                    'summary' => $response['summary'],
                ]);

                $this->package->update(['current_version' => $this->newVersion]);
            });
        } catch (Throwable $exception) {
            $this->changelog->update(['status' => ChangelogStatus::Backlog]);

            throw $exception;
        } finally {
            if ($basePath !== null) {
                File::deleteDirectory($basePath);
            }
        }
    }

    /**
     * Build the prompt sent to the changelog generator agent.
     */
    private function buildPrompt(DirectoryDiff $diff): string
    {
        return sprintf(
            "Analyze the following unified diff for package %s between versions %s and %s:\n\n%s",
            $this->package->name,
            $this->oldVersion,
            $this->newVersion,
            $this->formatDiff($diff)
        );
    }

    /**
     * Format the directory diff as a string for the agent prompt.
     */
    private function formatDiff(DirectoryDiff $diff): string
    {
        $sections = [];

        foreach ($diff->changes as $change) {
            if ($change->status === FileChangeStatus::Unchanged) {
                continue;
            }

            $sections[] = "File: {$change->path} ({$change->status->value})";

            if ($change->unifiedDiff !== null) {
                $sections[] = $change->unifiedDiff;
            }
        }

        return implode("\n\n", $sections);
    }

    /**
     * Persist the AI-generated changes to the database.
     */
    private function persistGeneratedChanges(StructuredAgentResponse $response): void
    {
        foreach ($response['changes'] as $change) {
            ChangelogChange::create([
                'changelog_id' => $this->changelog->id,
                'type' => ChangelogChangeType::from($change['type']),
                'title' => $change['title'],
                'message' => $change['message'],
            ]);
        }
    }
}
