<?php

namespace NietThijmen\ComposerChangelog\Process;

use Illuminate\Process\Factory;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;

final class LaravelProcessRunner implements ProcessRunner
{
    public function __construct(
        private readonly Factory $factory,
    ) {}

    public function run(array $command): void
    {
        $result = $this->factory->newPendingProcess()->command($command)->run();

        if ($result->failed()) {
            throw DownloadFailedException::gitCloneFailed(
                implode(' ', $command),
                $result->errorOutput()
            );
        }
    }
}
