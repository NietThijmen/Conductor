<?php

namespace NietThijmen\ComposerChangelog\Contracts;

use App\Models\Registry;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use NietThijmen\ComposerChangelog\Exceptions\UnsupportedPackageTypeException;

interface PackageDownloader
{
    /**
     * Download a specific package version into the given destination.
     *
     * @return string The absolute path to the downloaded package directory.
     *
     * @throws DownloadFailedException
     * @throws UnsupportedPackageTypeException
     */
    public function download(Registry $registry, string $package, string $version, string $destination): string;
}
