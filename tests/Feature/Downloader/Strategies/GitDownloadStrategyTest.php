<?php

use App\Http\Integrations\Composer\Data\PackageVersion;
use NietThijmen\ComposerChangelog\Downloader\Strategies\GitDownloadStrategy;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use NietThijmen\ComposerChangelog\Process\ProcessRunner;

it('supports git source versions', function () {
    $strategy = new GitDownloadStrategy(new class implements ProcessRunner
    {
        public function run(array $command): void {}
    });

    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'source' => ['type' => 'git', 'url' => 'https://github.com/laravel/framework.git'],
    ]);

    expect($strategy->supports($version))->toBeTrue();
});

it('does not support non-git versions', function () {
    $strategy = new GitDownloadStrategy(new class implements ProcessRunner
    {
        public function run(array $command): void {}
    });

    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'source' => ['type' => 'svn', 'url' => 'https://svn.example.com/package'],
    ]);

    expect($strategy->supports($version))->toBeFalse();
});

it('clones a git repository using the shallow clone strategy', function () {
    $executedCommands = [];

    $runner = new class($executedCommands) implements ProcessRunner
    {
        public function __construct(public array &$executedCommands) {}

        public function run(array $command): void
        {
            $this->executedCommands[] = $command;
        }
    };

    $strategy = new GitDownloadStrategy($runner);
    $destination = sys_get_temp_dir().'/composer-git-'.uniqid();

    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'source' => [
            'type' => 'git',
            'url' => 'https://github.com/laravel/framework.git',
            'reference' => 'abc123',
        ],
    ]);

    $path = $strategy->download($version, $destination);

    expect($path)->toBe($destination)
        ->and($executedCommands)->toHaveCount(1)
        ->and($executedCommands[0])->toBe([
            'git', 'clone', '--depth', '1', '--branch', 'v11.0.0',
            'https://github.com/laravel/framework.git', $destination,
        ]);

    deleteDirectory($destination);
});

it('falls back to full clone and checkout when shallow clone fails', function () {
    $callCount = 0;
    $commands = [];

    $runner = new class($callCount, $commands) implements ProcessRunner
    {
        public function __construct(
            public int &$callCount,
            public array &$commands,
        ) {}

        public function run(array $command): void
        {
            $this->callCount++;
            $this->commands[] = $command;

            if ($command[1] === 'clone' && $command[2] === '--depth') {
                throw DownloadFailedException::gitCloneFailed(
                    implode(' ', $command),
                    'shallow clone failed'
                );
            }
        }
    };

    $strategy = new GitDownloadStrategy($runner);
    $destination = sys_get_temp_dir().'/composer-git-'.uniqid();

    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'source' => [
            'type' => 'git',
            'url' => 'https://github.com/laravel/framework.git',
            'reference' => 'abc123',
        ],
    ]);

    $path = $strategy->download($version, $destination);

    expect($path)->toBe($destination)
        ->and($callCount)->toBe(3)
        ->and($commands[1])->toBe([
            'git', 'clone', 'https://github.com/laravel/framework.git', $destination,
        ])
        ->and($commands[2])->toBe([
            'git', '-C', $destination, 'checkout', 'abc123',
        ]);

    deleteDirectory($destination);
});

it('throws when no git url is available', function () {
    $strategy = new GitDownloadStrategy(new class implements ProcessRunner
    {
        public function run(array $command): void {}
    });

    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'source' => ['type' => 'git', 'url' => ''],
    ]);

    expect(fn () => $strategy->download($version, sys_get_temp_dir().'/test'))
        ->toThrow(DownloadFailedException::class);
});
