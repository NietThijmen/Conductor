<?php

namespace NietThijmen\ComposerChangelog\Data;

use NietThijmen\ComposerChangelog\Enums\FileChangeStatus;

/**
 * Represents the diff between two directories.
 *
 * @property-read string $oldPath
 * @property-read string $newPath
 * @property-read array<int, FileChange> $changes
 */
final class DirectoryDiff
{
    /**
     * @param  array<int, FileChange>  $changes
     */
    public function __construct(
        public readonly string $oldPath,
        public readonly string $newPath,
        public readonly array $changes = [],
    ) {}

    /**
     * @return array<int, FileChange>
     */
    public function added(): array
    {
        return $this->filterByStatus(FileChangeStatus::Added);
    }

    /**
     * @return array<int, FileChange>
     */
    public function removed(): array
    {
        return $this->filterByStatus(FileChangeStatus::Removed);
    }

    /**
     * @return array<int, FileChange>
     */
    public function modified(): array
    {
        return $this->filterByStatus(FileChangeStatus::Modified);
    }

    /**
     * @return array<int, FileChange>
     */
    public function unchanged(): array
    {
        return $this->filterByStatus(FileChangeStatus::Unchanged);
    }

    /**
     * @return array<int, FileChange>
     */
    private function filterByStatus(FileChangeStatus $status): array
    {
        return array_values(array_filter(
            $this->changes,
            fn (FileChange $change): bool => $change->status === $status
        ));
    }
}
