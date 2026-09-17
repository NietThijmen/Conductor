<?php

namespace NietThijmen\ComposerChangelog\Diff;

use InvalidArgumentException;
use NietThijmen\ComposerChangelog\Contracts\FileSystemDiffer as FileSystemDifferContract;
use NietThijmen\ComposerChangelog\Data\DirectoryDiff;
use NietThijmen\ComposerChangelog\Data\FileChange;
use NietThijmen\ComposerChangelog\Enums\FileChangeStatus;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use SplFileInfo;

final class FileSystemDiffer implements FileSystemDifferContract
{
    public function __construct(
        private readonly ?Differ $differ = null,
    ) {}

    /**
     * Compare two directories and return a structured diff.
     *
     * @throws InvalidArgumentException
     */
    public function compare(string $oldDirectory, string $newDirectory): DirectoryDiff
    {
        $this->guardDirectory($oldDirectory);
        $this->guardDirectory($newDirectory);

        $oldFiles = $this->collectFiles($oldDirectory);
        $newFiles = $this->collectFiles($newDirectory);

        $paths = array_unique([...array_keys($oldFiles), ...array_keys($newFiles)]);
        sort($paths);

        $changes = [];

        foreach ($paths as $path) {
            $changes[] = $this->diffFile($path, $oldFiles, $newFiles);
        }

        return new DirectoryDiff(
            oldPath: $oldDirectory,
            newPath: $newDirectory,
            changes: $changes,
        );
    }

    /**
     * Ensure the given path is a readable directory.
     *
     * @throws InvalidArgumentException
     */
    private function guardDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            throw new InvalidArgumentException("[{$directory}] is not a directory.");
        }

        if (! is_readable($directory)) {
            throw new InvalidArgumentException("[{$directory}] is not readable.");
        }
    }

    /**
     * Collect all files under a directory keyed by their relative path.
     *
     * @return array<string, string>
     */
    private function collectFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if (! $fileInfo->isFile()) {
                continue;
            }

            if (! in_array($fileInfo->getExtension(), ['php', 'json', 'lock', 'md'])) {
                continue;
            }

            $relativePath = $this->relativePath($fileInfo->getPathname(), $directory);
            $files[$relativePath] = $fileInfo->getPathname();
        }

        return $files;
    }

    /**
     * Compute the relative path of a file against a base directory.
     */
    private function relativePath(string $filePath, string $baseDirectory): string
    {
        $baseDirectory = rtrim($baseDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_replace($baseDirectory, '', $filePath);
    }

    /**
     * Compare a single file across the two directory snapshots.
     *
     * @param  array<string, string>  $oldFiles
     * @param  array<string, string>  $newFiles
     */
    private function diffFile(string $path, array $oldFiles, array $newFiles): FileChange
    {
        $oldExists = array_key_exists($path, $oldFiles);
        $newExists = array_key_exists($path, $newFiles);

        if ($oldExists && ! $newExists) {
            return new FileChange(
                path: $path,
                status: FileChangeStatus::Removed,
                oldContent: $this->readFile($oldFiles[$path]),
                newContent: null,
            );
        }

        if (! $oldExists && $newExists) {
            return new FileChange(
                path: $path,
                status: FileChangeStatus::Added,
                oldContent: null,
                newContent: $this->readFile($newFiles[$path]),
            );
        }

        $oldContent = $this->readFile($oldFiles[$path]);
        $newContent = $this->readFile($newFiles[$path]);

        if ($oldContent === $newContent) {
            return new FileChange(
                path: $path,
                status: FileChangeStatus::Unchanged,
                oldContent: $oldContent,
                newContent: $newContent,
            );
        }

        return new FileChange(
            path: $path,
            status: FileChangeStatus::Modified,
            oldContent: $oldContent,
            newContent: $newContent,
            unifiedDiff: $this->computeUnifiedDiff($path, $oldContent, $newContent),
        );
    }

    /**
     * Read the contents of a file, returning null if it cannot be read.
     */
    private function readFile(string $path): ?string
    {
        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    /**
     * Compute a unified diff for two file contents.
     */
    private function computeUnifiedDiff(string $path, string $oldContent, string $newContent): ?string
    {
        if (FileChange::isBinary($oldContent) || FileChange::isBinary($newContent)) {
            return null;
        }

        $differ = $this->differ ?? new Differ(new StrictUnifiedDiffOutputBuilder([
            'fromFile' => 'a/'.$path,
            'toFile' => 'b/'.$path,
        ]));

        return $differ->diff($oldContent, $newContent);
    }
}
