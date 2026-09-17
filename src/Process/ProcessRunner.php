<?php

namespace NietThijmen\ComposerChangelog\Process;

use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;

interface ProcessRunner
{
    /**
     * Run a command and throw if it fails.
     *
     * @param  array<int, string>  $command
     *
     * @throws DownloadFailedException
     */
    public function run(array $command): void;
}
