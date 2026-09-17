<?php

namespace NietThijmen\ComposerChangelog\Downloader\Strategies;

use App\Http\Integrations\Composer\Data\PackageVersion;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;

interface DownloadStrategy
{
    /**
     * Determine whether this strategy can handle the given package version.
     */
    public function supports(PackageVersion $version): bool;

    /**
     * Download the package version into the destination directory.
     *
     * @throws DownloadFailedException
     */
    public function download(PackageVersion $version, string $destination): string;
}
