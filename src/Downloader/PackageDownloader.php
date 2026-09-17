<?php

namespace NietThijmen\ComposerChangelog\Downloader;

use App\Http\Integrations\Composer\Composer;
use App\Http\Integrations\Composer\Requests\GetPackageMetadataRequest;
use GuzzleHttp\Client;
use NietThijmen\ComposerChangelog\Contracts\PackageDownloader as PackageDownloaderContract;
use NietThijmen\ComposerChangelog\Downloader\Strategies\DownloadStrategy;
use NietThijmen\ComposerChangelog\Downloader\Strategies\GitDownloadStrategy;
use NietThijmen\ComposerChangelog\Downloader\Strategies\ZipDownloadStrategy;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use NietThijmen\ComposerChangelog\Exceptions\UnsupportedPackageTypeException;
use NietThijmen\ComposerChangelog\Process\SymfonyProcessRunner;

final class PackageDownloader implements PackageDownloaderContract
{
    /**
     * @param  array<int, DownloadStrategy>  $strategies
     */
    public function __construct(
        private readonly Composer $composer,
        private readonly array $strategies = [],
    ) {}

    /**
     * Download a specific package version into the given destination.
     *
     * @throws DownloadFailedException
     * @throws UnsupportedPackageTypeException
     */
    public function download(string $package, string $version, string $destination): string
    {
        $metadata = $this->composer
            ->send(new GetPackageMetadataRequest($package))
            ->dtoOrFail();

        $packageVersion = $metadata->findVersion($version);

        if ($packageVersion === null) {
            throw DownloadFailedException::versionNotFound($package, $version);
        }

        $targetDirectory = $this->resolveTargetDirectory($package, $version, $destination);

        foreach ($this->strategies() as $strategy) {
            if ($strategy->supports($packageVersion)) {
                return $strategy->download($packageVersion, $targetDirectory);
            }
        }

        throw UnsupportedPackageTypeException::forVersion($packageVersion);
    }

    /**
     * @return array<int, DownloadStrategy>
     */
    private function strategies(): array
    {
        if ($this->strategies !== []) {
            return $this->strategies;
        }

        return [
            new ZipDownloadStrategy(new Client),
            new GitDownloadStrategy(new SymfonyProcessRunner),
        ];
    }

    private function resolveTargetDirectory(string $package, string $version, string $destination): string
    {
        $safePackage = str_replace('/', DIRECTORY_SEPARATOR, $package);

        return rtrim($destination, DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .$safePackage
            .DIRECTORY_SEPARATOR
            .$version;
    }
}
