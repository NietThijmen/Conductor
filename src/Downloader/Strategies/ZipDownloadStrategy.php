<?php

namespace NietThijmen\ComposerChangelog\Downloader\Strategies;

use App\Http\Integrations\Composer\Data\PackageVersion;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Log;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use RuntimeException;
use ZipArchive;

final class ZipDownloadStrategy implements DownloadStrategy
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {}

    public function supports(PackageVersion $version): bool
    {
        return ($version->dist['type'] ?? null) === 'zip' && ! empty($version->dist['url']);
    }

    public function download(PackageVersion $version, string $destination): string
    {
        $url = $version->dist['url'] ?? null;

        if (empty($url) || ! is_string($url)) {
            throw DownloadFailedException::noDownloadUrl($version->name, $version->version);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'composer-zip-');

        if ($tempFile === false) {
            throw new RuntimeException('Unable to create temporary file for zip download.');
        }

        try {
            $this->client->request(
                'GET',
                $url,
                [
                    'sink' => $tempFile,
                    'User-Agent' => 'Composer Changelog (https://github.com/nietthijmen/composer-changelog)',
                    'accept' => 'application/zip',
                    'Connection' => 'close',
                ]);

            $this->extract($tempFile, $destination);
            $this->flattenDirectory($destination);
        } catch (GuzzleException $exception) {
            Log::error("Failed to download zip from {$url}: {$exception->getMessage()}");
            throw $exception; // throw the exception up to fail "gracefully"
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return $destination;
    }

    /**
     * Extract a zip archive to the given destination.
     *
     * @throws DownloadFailedException
     */
    private function extract(string $zipPath, string $destination): void
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw DownloadFailedException::zipOpenFailed($zipPath);
        }

        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
            $zip->close();

            throw DownloadFailedException::extractionFailed($zipPath, $destination);
        }

        if (! $zip->extractTo($destination)) {
            $zip->close();

            throw DownloadFailedException::extractionFailed($zipPath, $destination);
        }

        $zip->close();
    }

    /**
     * If the extracted archive contains a single root directory, move its contents up.
     */
    private function flattenDirectory(string $destination): void
    {
        $entries = array_diff(scandir($destination) ?: [], ['.', '..']);

        if (count($entries) !== 1) {
            return;
        }

        $rootName = array_values($entries)[0];
        $root = $destination.DIRECTORY_SEPARATOR.$rootName;

        if (! is_dir($root)) {
            return;
        }

        $temp = $destination.DIRECTORY_SEPARATOR.'__tmp_'.uniqid();

        if (! rename($root, $temp)) {
            throw new RuntimeException("Unable to move [{$root}] to [{$temp}].");
        }

        foreach (array_diff(scandir($temp) ?: [], ['.', '..']) as $entry) {
            if (! rename($temp.DIRECTORY_SEPARATOR.$entry, $destination.DIRECTORY_SEPARATOR.$entry)) {
                throw new RuntimeException("Unable to move [{$entry}] to [{$destination}].");
            }
        }

        rmdir($temp);
    }
}
