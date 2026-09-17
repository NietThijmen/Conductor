<?php

namespace NietThijmen\ComposerChangelog\Data;

use NietThijmen\ComposerChangelog\Enums\FileChangeStatus;

/**
 * Represents a single file change between two directory snapshots.
 *
 * @property-read string $path
 * @property-read FileChangeStatus $status
 * @property-read string|null $oldContent
 * @property-read string|null $newContent
 * @property-read string|null $unifiedDiff
 */
final class FileChange
{
    public function __construct(
        public readonly string $path,
        public readonly FileChangeStatus $status,
        public readonly ?string $oldContent,
        public readonly ?string $newContent,
        public readonly ?string $unifiedDiff = null,
    ) {}

    /**
     * Determine whether the file content is considered binary.
     */
    public static function isBinary(string $content): bool
    {
        return str_contains($content, "\0");
    }
}
