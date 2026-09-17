<?php

namespace NietThijmen\ComposerChangelog\Exceptions;

use Exception;

final class DownloadFailedException extends Exception
{
    public static function versionNotFound(string $package, string $version): self
    {
        return new self("Version [{$version}] not found for package [{$package}].");
    }

    public static function noDownloadUrl(string $package, string $version): self
    {
        return new self("No dist or source URL found for [{$package}:{$version}].");
    }

    public static function zipOpenFailed(string $path): self
    {
        return new self("Unable to open zip archive at [{$path}].");
    }

    public static function extractionFailed(string $path, string $destination): self
    {
        return new self("Unable to extract [{$path}] to [{$destination}].");
    }

    public static function gitCloneFailed(string $command, string $output): self
    {
        return new self("Git clone failed: {$command}\n{$output}");
    }
}
