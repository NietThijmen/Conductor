<?php

use NietThijmen\ComposerChangelog\Data\DirectoryDiff;
use NietThijmen\ComposerChangelog\Data\FileChange;
use NietThijmen\ComposerChangelog\Diff\FileSystemDiffer;
use NietThijmen\ComposerChangelog\Enums\FileChangeStatus;

function createTempDirectory(): string
{
    $path = sys_get_temp_dir().'/composer-diff-'.uniqid();
    mkdir($path, 0755, true);

    return $path;
}

function writeFile(string $directory, string $path, string $content): void
{
    $fullPath = $directory.DIRECTORY_SEPARATOR.$path;
    $dir = dirname($fullPath);

    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($fullPath, $content);
}

it('detects added files', function () {
    $old = createTempDirectory();
    $new = createTempDirectory();

    writeFile($old, 'keep.txt', 'kept');
    writeFile($new, 'keep.txt', 'kept');
    writeFile($new, 'added.txt', 'new content');

    $diff = (new FileSystemDiffer)->compare($old, $new);

    expect($diff)->toBeInstanceOf(DirectoryDiff::class)
        ->and($diff->added())->toHaveCount(1)
        ->and($diff->added()[0]->path)->toBe('added.txt')
        ->and($diff->added()[0]->status)->toBe(FileChangeStatus::Added)
        ->and($diff->added()[0]->newContent)->toBe('new content')
        ->and($diff->added()[0]->oldContent)->toBeNull();

    deleteDirectory($old);
    deleteDirectory($new);
});

it('detects removed files', function () {
    $old = createTempDirectory();
    $new = createTempDirectory();

    writeFile($old, 'keep.txt', 'kept');
    writeFile($old, 'removed.txt', 'old content');
    writeFile($new, 'keep.txt', 'kept');

    $diff = (new FileSystemDiffer)->compare($old, $new);

    expect($diff->removed())->toHaveCount(1)
        ->and($diff->removed()[0]->path)->toBe('removed.txt')
        ->and($diff->removed()[0]->status)->toBe(FileChangeStatus::Removed)
        ->and($diff->removed()[0]->oldContent)->toBe('old content')
        ->and($diff->removed()[0]->newContent)->toBeNull();

    deleteDirectory($old);
    deleteDirectory($new);
});

it('detects modified files with unified diff', function () {
    $old = createTempDirectory();
    $new = createTempDirectory();

    writeFile($old, 'changed.txt', "line1\nline2\nline3\n");
    writeFile($new, 'changed.txt', "line1\nmodified\nline3\n");

    $diff = (new FileSystemDiffer)->compare($old, $new);

    expect($diff->modified())->toHaveCount(1)
        ->and($diff->modified()[0]->path)->toBe('changed.txt')
        ->and($diff->modified()[0]->status)->toBe(FileChangeStatus::Modified)
        ->and($diff->modified()[0]->unifiedDiff)->toContain('--- a/changed.txt')
        ->and($diff->modified()[0]->unifiedDiff)->toContain('+++ b/changed.txt')
        ->and($diff->modified()[0]->unifiedDiff)->toContain('-line2')
        ->and($diff->modified()[0]->unifiedDiff)->toContain('+modified');

    deleteDirectory($old);
    deleteDirectory($new);
});

it('detects unchanged files', function () {
    $old = createTempDirectory();
    $new = createTempDirectory();

    writeFile($old, 'same.txt', 'same content');
    writeFile($new, 'same.txt', 'same content');

    $diff = (new FileSystemDiffer)->compare($old, $new);

    expect($diff->unchanged())->toHaveCount(1)
        ->and($diff->unchanged()[0]->path)->toBe('same.txt')
        ->and($diff->unchanged()[0]->status)->toBe(FileChangeStatus::Unchanged);

    deleteDirectory($old);
    deleteDirectory($new);
});

it('recursively detects nested changes', function () {
    $old = createTempDirectory();
    $new = createTempDirectory();

    writeFile($old, 'src/Console/Command.php', 'old');
    writeFile($new, 'src/Console/Command.php', 'new');
    writeFile($new, 'src/Http/Controller.php', 'added');

    $diff = (new FileSystemDiffer)->compare($old, $new);

    expect($diff->modified())->toHaveCount(1)
        ->and($diff->modified()[0]->path)->toBe('src/Console/Command.php')
        ->and($diff->added())->toHaveCount(1)
        ->and($diff->added()[0]->path)->toBe('src/Http/Controller.php');

    deleteDirectory($old);
    deleteDirectory($new);
});

it('skips unified diff for binary files', function () {
    $old = createTempDirectory();
    $new = createTempDirectory();

    writeFile($old, 'image.png', "PNG\0binary");
    writeFile($new, 'image.png', "PNG\0different");

    $diff = (new FileSystemDiffer)->compare($old, $new);

    expect($diff->modified())->toHaveCount(1)
        ->and($diff->modified()[0]->unifiedDiff)->toBeNull();

    deleteDirectory($old);
    deleteDirectory($new);
});

it('throws for non-existent directories', function () {
    expect(fn () => (new FileSystemDiffer)->compare('/does/not/exist', sys_get_temp_dir()))
        ->toThrow(InvalidArgumentException::class);
});

it('filters directory diff changes by status', function () {
    $changes = [
        new FileChange('a.txt', FileChangeStatus::Added, null, 'a'),
        new FileChange('b.txt', FileChangeStatus::Removed, 'b', null),
        new FileChange('c.txt', FileChangeStatus::Modified, 'old', 'new', 'diff'),
        new FileChange('d.txt', FileChangeStatus::Unchanged, 'd', 'd'),
    ];

    $diff = new DirectoryDiff('/old', '/new', $changes);

    expect($diff->added())->toHaveCount(1)
        ->and($diff->removed())->toHaveCount(1)
        ->and($diff->modified())->toHaveCount(1)
        ->and($diff->unchanged())->toHaveCount(1);
});

it('detects binary content', function () {
    expect(FileChange::isBinary("hello\0world"))->toBeTrue()
        ->and(FileChange::isBinary('hello world'))->toBeFalse();
});
