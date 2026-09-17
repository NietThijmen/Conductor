<?php

use Illuminate\Process\Factory;
use Illuminate\Support\Facades\Process;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use NietThijmen\ComposerChangelog\Process\LaravelProcessRunner;

it('runs a successful command via the laravel process factory', function () {
    Process::fake();

    $runner = new LaravelProcessRunner(app(Factory::class));
    $runner->run(['git', 'clone', 'https://github.com/laravel/framework.git', '/tmp/framework']);

    Process::assertRan(fn ($process) => $process->command === [
        'git', 'clone', 'https://github.com/laravel/framework.git', '/tmp/framework',
    ]);
});

it('throws when the laravel process fails', function () {
    Process::fake(fn () => Process::result('', 'fatal: repository not found', 128));

    $runner = new LaravelProcessRunner(app(Factory::class));

    expect(fn () => $runner->run(['git', 'clone', 'https://example.com/missing.git', '/tmp/missing']))
        ->toThrow(DownloadFailedException::class, 'fatal: repository not found');
});
