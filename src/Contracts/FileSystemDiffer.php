<?php

namespace NietThijmen\ComposerChangelog\Contracts;

use NietThijmen\ComposerChangelog\Data\DirectoryDiff;

interface FileSystemDiffer
{
    /**
     * Compare two directories and return a structured diff.
     *
     * @throws \InvalidArgumentException
     */
    public function compare(string $oldDirectory, string $newDirectory): DirectoryDiff;
}
