<?php

namespace NietThijmen\ComposerChangelog\Exceptions;

use App\Http\Integrations\Composer\Data\PackageVersion;
use Exception;

final class UnsupportedPackageTypeException extends Exception
{
    public static function forVersion(PackageVersion $version): self
    {
        $distType = $version->dist['type'] ?? 'none';
        $sourceType = $version->source['type'] ?? 'none';

        return new self(
            "No download strategy available for [{$version->name}:{$version->version}] (dist: {$distType}, source: {$sourceType})."
        );
    }
}
