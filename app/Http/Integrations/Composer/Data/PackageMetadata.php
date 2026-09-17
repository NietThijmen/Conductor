<?php

namespace App\Http\Integrations\Composer\Data;

use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Traits\Responses\HasResponse;

/**
 * Represents the metadata response for a Composer package from Packagist P2.
 *
 * @property-read string $packageName
 * @property-read array<int, PackageVersion> $versions
 */
final class PackageMetadata implements WithResponse
{
    use HasResponse;

    /**
     * @param  array<int, PackageVersion>  $versions
     */
    public function __construct(
        public readonly string $packageName,
        public readonly array $versions = [],
    ) {}

    /**
     * Build a DTO from a raw P2 metadata response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $packages = $data['packages'] ?? [];

        if (! is_array($packages) || $packages === []) {
            return new self(packageName: '');
        }

        $packageName = array_key_first($packages);
        $versions = [];

        foreach ((array) ($packages[$packageName] ?? []) as $versionData) {
            if (is_array($versionData)) {
                $versions[] = PackageVersion::fromArray($versionData);
            }
        }

        return new self(
            packageName: (string) $packageName,
            versions: $versions,
        );
    }

    /**
     * Get the latest version, or null if no versions are present.
     */
    public function latestVersion(): ?PackageVersion
    {
        return $this->versions[0] ?? null;
    }

    /**
     * Find a version by its exact version string.
     */
    public function findVersion(string $version): ?PackageVersion
    {
        foreach ($this->versions as $packageVersion) {
            if ($packageVersion->version === $version) {
                return $packageVersion;
            }
        }

        return null;
    }
}
