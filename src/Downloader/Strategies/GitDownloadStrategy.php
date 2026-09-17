<?php

namespace NietThijmen\ComposerChangelog\Downloader\Strategies;

use App\Http\Integrations\Composer\Data\PackageVersion;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use NietThijmen\ComposerChangelog\Process\ProcessRunner;

final class GitDownloadStrategy implements DownloadStrategy
{
    public function __construct(
        private readonly ProcessRunner $runner,
    ) {}

    public function supports(PackageVersion $version): bool
    {
        return ($version->source['type'] ?? null) === 'git' && ! empty($version->source['url']);
    }

    public function download(PackageVersion $version, string $destination): string
    {
        $url = $version->source['url'] ?? null;

        if (empty($url) || ! is_string($url)) {
            throw DownloadFailedException::noDownloadUrl($version->name, $version->version);
        }

        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
            throw DownloadFailedException::extractionFailed($url, $destination);
        }

        $reference = $version->source['reference'] ?? null;

        if ($this->tryShallowClone($version, $url, $destination)) {
            return $destination;
        }

        $this->cloneAndCheckout($url, $reference, $destination);

        return $destination;
    }

    /**
     * Attempt a shallow clone using the version as a branch or tag reference.
     */
    private function tryShallowClone(PackageVersion $version, string $url, string $destination): bool
    {
        try {
            $this->runner->run([
                'git', 'clone', '--depth', '1', '--branch', $version->version, $url, $destination,
            ]);

            return true;
        } catch (DownloadFailedException) {
            return false;
        }
    }

    /**
     * Fallback to a full clone followed by an optional checkout.
     */
    private function cloneAndCheckout(string $url, ?string $reference, string $destination): void
    {
        $this->runner->run(['git', 'clone', $url, $destination]);

        if (is_string($reference) && $reference !== '') {
            $this->runner->run(['git', '-C', $destination, 'checkout', $reference]);
        }
    }
}
