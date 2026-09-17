<?php

use App\Http\Integrations\Composer\Composer;
use App\Http\Integrations\Composer\Data\PackageVersion;
use App\Http\Integrations\Composer\Requests\GetPackageMetadataRequest;
use NietThijmen\ComposerChangelog\Contracts\PackageDownloader as PackageDownloaderContract;
use NietThijmen\ComposerChangelog\Downloader\PackageDownloader;
use NietThijmen\ComposerChangelog\Downloader\Strategies\DownloadStrategy;
use NietThijmen\ComposerChangelog\Exceptions\DownloadFailedException;
use NietThijmen\ComposerChangelog\Exceptions\UnsupportedPackageTypeException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('downloads a package using a matching strategy', function () {
    $strategy = new class implements DownloadStrategy
    {
        public function supports(PackageVersion $version): bool
        {
            return true;
        }

        public function download(PackageVersion $version, string $destination): string
        {
            if (! is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            file_put_contents($destination.'/downloaded.txt', 'ok');

            return $destination;
        }
    };

    $connector = new Composer;
    $connector->withMockClient(new MockClient([
        GetPackageMetadataRequest::class => MockResponse::make([
            'packages' => [
                'laravel/framework' => [
                    [
                        'name' => 'laravel/framework',
                        'version' => 'v11.0.0',
                        'dist' => ['type' => 'zip', 'url' => 'https://example.com/package.zip'],
                    ],
                ],
            ],
        ]),
    ]));

    $downloader = new PackageDownloader($connector, [$strategy]);
    $destination = sys_get_temp_dir().'/composer-downloader-'.uniqid();

    $path = $downloader->download('laravel/framework', 'v11.0.0', $destination);

    expect($path)->toBe($destination.'/laravel/framework/v11.0.0')
        ->and(file_exists($path.'/downloaded.txt'))->toBeTrue();

    deleteDirectory($destination);
});

it('throws when the requested version is not found', function () {
    $connector = new Composer;
    $connector->withMockClient(new MockClient([
        GetPackageMetadataRequest::class => MockResponse::make([
            'packages' => [
                'laravel/framework' => [
                    [
                        'name' => 'laravel/framework',
                        'version' => 'v10.0.0',
                    ],
                ],
            ],
        ]),
    ]));

    $downloader = new PackageDownloader($connector, []);

    expect(fn () => $downloader->download('laravel/framework', 'v11.0.0', sys_get_temp_dir()))
        ->toThrow(DownloadFailedException::class, 'Version [v11.0.0] not found');
});

it('throws when no strategy supports the version', function () {
    $connector = new Composer;
    $connector->withMockClient(new MockClient([
        GetPackageMetadataRequest::class => MockResponse::make([
            'packages' => [
                'laravel/framework' => [
                    [
                        'name' => 'laravel/framework',
                        'version' => 'v11.0.0',
                        'dist' => ['type' => 'tar', 'url' => 'https://example.com/package.tar'],
                    ],
                ],
            ],
        ]),
    ]));

    $downloader = new PackageDownloader($connector, []);

    expect(fn () => $downloader->download('laravel/framework', 'v11.0.0', sys_get_temp_dir()))
        ->toThrow(UnsupportedPackageTypeException::class);
});

it('implements the package downloader contract', function () {
    $connector = new Composer;

    expect(new PackageDownloader($connector))->toBeInstanceOf(PackageDownloaderContract::class);
});
