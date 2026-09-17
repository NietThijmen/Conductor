<?php

namespace App\Http\Integrations\Composer\Data;

use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Traits\Responses\HasResponse;

/**
 * Represents a single version of a Composer package returned by Packagist P2.
 *
 * @property-read string $name
 * @property-read string $version
 * @property-read string|null $versionNormalized
 * @property-read array<string, mixed>|null $source
 * @property-read array<string, mixed>|null $dist
 * @property-read array<string, mixed>|null $require
 * @property-read array<string, mixed>|null $requireDev
 * @property-read string|null $time
 * @property-read string|null $type
 * @property-read array<string, mixed>|null $extra
 */
final class PackageVersion implements WithResponse
{
    use HasResponse;

    /**
     * Create a new package version DTO from a P2 metadata entry.
     *
     * @param  array<string, mixed>|null  $source
     * @param  array<string, mixed>|null  $dist
     * @param  array<string, string>|null  $require
     * @param  array<string, string>|null  $requireDev
     * @param  array<string, mixed>|null  $extra
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public readonly string $name,
        public readonly string $version,
        public readonly ?string $versionNormalized,
        public readonly ?array $source,
        public readonly ?array $dist,
        public readonly ?array $require,
        public readonly ?array $requireDev,
        public readonly ?string $time,
        public readonly ?string $type,
        public readonly ?array $extra,
        public readonly array $raw = [],
    ) {}

    /**
     * Build a DTO from a raw P2 version entry.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            version: (string) ($data['version'] ?? ''),
            versionNormalized: isset($data['version_normalized']) ? (string) $data['version_normalized'] : null,
            source: isset($data['source']) && is_array($data['source']) ? $data['source'] : null,
            dist: isset($data['dist']) && is_array($data['dist']) ? $data['dist'] : null,
            require: isset($data['require']) && is_array($data['require']) ? $data['require'] : null,
            requireDev: isset($data['require-dev']) && is_array($data['require-dev']) ? $data['require-dev'] : null,
            time: isset($data['time']) ? (string) $data['time'] : null,
            type: isset($data['type']) ? (string) $data['type'] : null,
            extra: isset($data['extra']) && is_array($data['extra']) ? $data['extra'] : null,
            raw: $data,
        );
    }

    /**
     * Get the download URL for this version, preferring dist over source.
     */
    public function downloadUrl(): ?string
    {
        return $this->dist['url'] ?? $this->source['url'] ?? null;
    }
}
