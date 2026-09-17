<?php

use App\Http\Integrations\Composer\Data\PackageVersion;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use NietThijmen\ComposerChangelog\Downloader\Strategies\ZipDownloadStrategy;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;

function createZipArchive(string $path, array $entries): void
{
    $zip = new ZipArchive;

    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("Unable to create zip at [{$path}].");
    }

    foreach ($entries as $name => $content) {
        $zip->addFromString($name, $content);
    }

    $zip->close();
}

function createMockGuzzleClient(string $body): Client
{
    $mock = new MockHandler([new Response(200, [], $body)]);

    return new Client(['handler' => HandlerStack::create($mock)]);
}

it('supports zip dist versions', function () {
    $strategy = new ZipDownloadStrategy(new Client);
    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'dist' => ['type' => 'zip', 'url' => 'https://example.com/package.zip'],
    ]);

    expect($strategy->supports($version))->toBeTrue();
});

it('does not support non-zip versions', function () {
    $strategy = new ZipDownloadStrategy(new Client);
    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'dist' => ['type' => 'tar', 'url' => 'https://example.com/package.tar'],
    ]);

    expect($strategy->supports($version))->toBeFalse();
});

it('downloads and extracts a zip archive', function () {
    $zipPath = tempnam(sys_get_temp_dir(), 'test-zip-');
    createZipArchive($zipPath, [
        'laravel-framework-abc123/composer.json' => '{"name":"laravel/framework"}',
        'laravel-framework-abc123/src/Container.php' => '<?php',
    ]);

    $body = file_get_contents($zipPath);
    unlink($zipPath);

    $strategy = new ZipDownloadStrategy(createMockGuzzleClient($body));
    $destination = sys_get_temp_dir().'/composer-download-'.uniqid();

    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'dist' => ['type' => 'zip', 'url' => 'https://example.com/package.zip'],
    ]);

    $path = $strategy->download($version, $destination);

    expect($path)->toBe($destination)
        ->and(file_exists($destination.'/composer.json'))->toBeTrue()
        ->and(file_exists($destination.'/src/Container.php'))->toBeTrue()
        ->and(file_exists($destination.'/laravel-framework-abc123'))->toBeFalse();

    // Cleanup
    deleteDirectory($destination);
});

it('throws when no zip url is available', function () {
    $strategy = new ZipDownloadStrategy(new Client);
    $version = PackageVersion::fromArray([
        'name' => 'laravel/framework',
        'version' => 'v11.0.0',
        'dist' => ['type' => 'zip', 'url' => ''],
    ]);

    expect(fn () => $strategy->download($version, sys_get_temp_dir().'/test'))
        ->toThrow(DownloadFailedException::class);
});
