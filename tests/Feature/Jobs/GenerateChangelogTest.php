<?php

use App\Ai\Agents\ChangelogGenerator;
use App\Enums\ChangelogChangeType;
use App\Enums\ChangelogStatus;
use App\Jobs\GenerateChangelog;
use App\Models\Package;
use Illuminate\Support\Facades\Bus;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredAgentResponse;
use NietThijmen\ComposerChangelog\Contracts\FileSystemDiffer;
use NietThijmen\ComposerChangelog\Contracts\PackageDownloader;
use NietThijmen\ComposerChangelog\Data\DirectoryDiff;
use NietThijmen\ComposerChangelog\Data\FileChange;
use NietThijmen\ComposerChangelog\Enums\FileChangeStatus;

it('downloads both versions, diffs them, and persists an AI-generated changelog', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/framework',
        'current_version' => '1.0.0',
    ]);

    $downloader = Mockery::mock(PackageDownloader::class);
    $downloader->shouldReceive('download')
        ->once()
        ->with($package->name, '1.0.0', Mockery::pattern('/package-versions/'))
        ->andReturn('/tmp/package-versions/old/laravel/framework/1.0.0');
    $downloader->shouldReceive('download')
        ->once()
        ->with($package->name, '2.0.0', Mockery::pattern('/package-versions/'))
        ->andReturn('/tmp/package-versions/new/laravel/framework/2.0.0');

    $differ = Mockery::mock(FileSystemDiffer::class);
    $differ->shouldReceive('compare')
        ->once()
        ->with('/tmp/package-versions/old/laravel/framework/1.0.0', '/tmp/package-versions/new/laravel/framework/2.0.0')
        ->andReturn(new DirectoryDiff(
            oldPath: '/tmp/package-versions/old/laravel/framework/1.0.0',
            newPath: '/tmp/package-versions/new/laravel/framework/2.0.0',
            changes: [
                new FileChange(
                    path: 'src/Helper.php',
                    status: FileChangeStatus::Modified,
                    oldContent: 'old',
                    newContent: 'new',
                    unifiedDiff: 'diff output',
                ),
            ],
        ));

    $agent = Mockery::mock(ChangelogGenerator::class);
    $agent->shouldReceive('prompt')
        ->once()
        ->with(Mockery::pattern('/Analyze the following unified diff/'))
        ->andReturn(new StructuredAgentResponse(
            invocationId: 'fake-invocation',
            structured: [
                'title' => 'Laravel framework 2.0.0 changelog',
                'summary' => 'A helpful summary of the update.',
                'changes' => [
                    [
                        'type' => 'updated',
                        'title' => 'Refactored helper',
                        'message' => 'The helper now behaves differently.',
                    ],
                    [
                        'type' => 'new',
                        'title' => 'New feature',
                        'message' => 'A new feature was added.',
                    ],
                ],
            ],
            text: '',
            usage: new Usage,
            meta: new Meta,
        ));

    $this->app->instance(PackageDownloader::class, $downloader);
    $this->app->instance(FileSystemDiffer::class, $differ);
    $this->app->instance(ChangelogGenerator::class, $agent);

    $job = new GenerateChangelog($package, '1.0.0', '2.0.0');

    expect($job->changelog)->not->toBeNull()
        ->and($job->changelog->status)->toBe(ChangelogStatus::Backlog);

    $job->handle($downloader, $differ, $agent);

    $package->refresh();
    expect($package->current_version)->toBe('2.0.0');

    $changelog = $package->changelogs()->first();
    expect($changelog)->not->toBeNull()
        ->and($changelog->old_version)->toBe('1.0.0')
        ->and($changelog->new_version)->toBe('2.0.0')
        ->and($changelog->status)->toBe(ChangelogStatus::Checked)
        ->and($changelog->title)->toBe('Laravel framework 2.0.0 changelog')
        ->and($changelog->summary)->toBe('A helpful summary of the update.')
        ->and($changelog->changes)->toHaveCount(2);

    $changes = $changelog->changes->sortBy(fn ($change) => $change->title)->values();
    expect($changes[0]->type)->toBe(ChangelogChangeType::New)
        ->and($changes[0]->title)->toBe('New feature')
        ->and($changes[0]->message)->toBe('A new feature was added.')
        ->and($changes[1]->type)->toBe(ChangelogChangeType::Updated)
        ->and($changes[1]->title)->toBe('Refactored helper')
        ->and($changes[1]->message)->toBe('The helper now behaves differently.');
});

it('rolls the changelog back to backlog when generation fails', function () {
    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/framework',
        'current_version' => '1.0.0',
    ]);

    $downloader = Mockery::mock(PackageDownloader::class);
    $downloader->shouldReceive('download')
        ->andThrow(new RuntimeException('Network error'));

    $differ = Mockery::mock(FileSystemDiffer::class);
    $agent = Mockery::mock(ChangelogGenerator::class);

    $this->app->instance(PackageDownloader::class, $downloader);
    $this->app->instance(FileSystemDiffer::class, $differ);
    $this->app->instance(ChangelogGenerator::class, $agent);

    $job = new GenerateChangelog($package, '1.0.0', '2.0.0');

    expect($job->changelog)->not->toBeNull()
        ->and($job->changelog->status)->toBe(ChangelogStatus::Backlog);

    try {
        $job->handle($downloader, $differ, $agent);
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Network error');
    }

    $job->changelog->refresh();
    expect($job->changelog->status)->toBe(ChangelogStatus::Backlog);
});

it('can be dispatched to the queue', function () {
    Bus::fake();

    $package = Package::factory()->withoutRegistry()->create([
        'name' => 'laravel/framework',
        'current_version' => '1.0.0',
    ]);

    GenerateChangelog::dispatch($package, '1.0.0', '2.0.0');

    Bus::assertDispatched(GenerateChangelog::class, function (GenerateChangelog $job) use ($package) {
        return $job->package->is($package)
            && $job->oldVersion === '1.0.0'
            && $job->newVersion === '2.0.0'
            && $job->changelog->status === ChangelogStatus::Backlog;
    });

    $changelog = $package->changelogs()->first();
    expect($changelog)->not->toBeNull()
        ->and($changelog->status)->toBe(ChangelogStatus::Backlog);
});
