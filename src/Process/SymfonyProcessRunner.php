<?php

namespace NietThijmen\ComposerChangelog\Process;

use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SymfonyProcessRunner implements ProcessRunner
{
    public function run(array $command): void
    {
        $process = new Process($command);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw DownloadFailedException::gitCloneFailed(
                $process->getCommandLine(),
                $process->getErrorOutput()
            );
        }
    }
}
