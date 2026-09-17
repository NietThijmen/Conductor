<?php

use App\Enums\RegistryAuthType;
use App\Http\Integrations\Composer\Composer;
use App\Http\Integrations\Composer\Data\PackageMetadata;
use App\Http\Integrations\Composer\Data\PackageVersion;
use App\Http\Integrations\Composer\Requests\GetPackageMetadataRequest;
use App\Models\Registry;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('resolves the public packagist repository by default', function () {
    $connector = new Composer;

    expect($connector->resolveBaseUrl())->toBe('https://repo.packagist.org');
});

it('resolves a custom registry url', function () {
    $registry = Registry::factory()->create([
        'url' => 'https://packages.example.com',
    ]);

    $connector = new Composer($registry);

    expect($connector->resolveBaseUrl())->toBe('https://packages.example.com');
});

it('does not add authentication for none auth type', function () {
    $registry = Registry::factory()->create([
        'auth_type' => RegistryAuthType::None,
        'auth_config' => [],
    ]);

    $connector = new Composer($registry);

    expect($connector->headers()->all())->toBeEmpty();
});

it('adds basic authentication from registry config', function () {
    $registry = Registry::factory()->create([
        'auth_type' => RegistryAuthType::Basic,
        'auth_config' => ['username' => 'user', 'password' => 'secret'],
    ]);

    $connector = new Composer($registry);
    $request = $connector->createPendingRequest(new GetPackageMetadataRequest('laravel/framework'));

    expect($request->headers()->get('Authorization'))->toBe('Basic '.base64_encode('user:secret'));
});

it('adds bearer token authentication from registry config', function () {
    $registry = Registry::factory()->create([
        'auth_type' => RegistryAuthType::Token,
        'auth_config' => ['token' => 'my-token'],
    ]);

    $connector = new Composer($registry);
    $request = $connector->createPendingRequest(new GetPackageMetadataRequest('laravel/framework'));

    expect($request->headers()->get('Authorization'))->toBe('Bearer my-token');
});

it('adds composer authentication from registry config', function () {
    $registry = Registry::factory()->create([
        'auth_type' => RegistryAuthType::Composer,
        'auth_config' => ['http-basic' => ['example.com' => ['username' => 'user', 'password' => 'secret']]],
    ]);

    $connector = new Composer($registry);
    $request = $connector->createPendingRequest(new GetPackageMetadataRequest('laravel/framework'));

    expect($request->headers()->get('COMPOSER-AUTH'))->toBe(
        'Basic '.base64_encode(json_encode($registry->auth_config, JSON_THROW_ON_ERROR))
    );
});

it('resolves the package metadata endpoint', function () {
    $request = new GetPackageMetadataRequest('laravel/framework');

    expect($request->resolveEndpoint())->toBe('/p2/laravel/framework.json');
});

it('resolves the dev metadata endpoint when requested', function () {
    $request = new GetPackageMetadataRequest('laravel/framework', includeDev: true);

    expect($request->resolveEndpoint())->toBe('/p2/laravel/framework~dev.json');
});

it('casts a p2 response into a metadata dto', function () {
    $mockClient = new MockClient([
        GetPackageMetadataRequest::class => MockResponse::make([
            'packages' => [
                'laravel/framework' => [
                    [
                        'name' => 'laravel/framework',
                        'version' => 'v11.0.0',
                        'version_normalized' => '11.0.0.0',
                        'source' => [
                            'url' => 'https://github.com/laravel/framework.git',
                            'type' => 'git',
                            'reference' => 'abc123',
                        ],
                        'dist' => [
                            'url' => 'https://api.github.com/repos/laravel/framework/zipball/abc123',
                            'type' => 'zip',
                            'shasum' => '',
                            'reference' => 'abc123',
                        ],
                        'require' => ['php' => '^8.2'],
                        'time' => '2024-03-12T00:00:00+00:00',
                        'type' => 'library',
                    ],
                    [
                        'name' => 'laravel/framework',
                        'version' => 'v10.0.0',
                        'version_normalized' => '10.0.0.0',
                        'source' => [
                            'url' => 'https://github.com/laravel/framework.git',
                            'type' => 'git',
                            'reference' => 'def456',
                        ],
                        'dist' => [
                            'url' => 'https://api.github.com/repos/laravel/framework/zipball/def456',
                            'type' => 'zip',
                            'shasum' => '',
                            'reference' => 'def456',
                        ],
                        'require' => ['php' => '^8.1'],
                        'time' => '2023-02-14T00:00:00+00:00',
                        'type' => 'library',
                    ],
                ],
            ],
        ]),
    ]);

    $connector = new Composer;
    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetPackageMetadataRequest('laravel/framework'));
    $metadata = $response->dtoOrFail();

    expect($metadata)
        ->toBeInstanceOf(PackageMetadata::class)
        ->and($metadata->packageName)->toBe('laravel/framework')
        ->and($metadata->versions)->toHaveCount(2);

    $latest = $metadata->latestVersion();

    expect($latest)
        ->toBeInstanceOf(PackageVersion::class)
        ->and($latest->version)->toBe('v11.0.0')
        ->and($latest->downloadUrl())->toBe('https://api.github.com/repos/laravel/framework/zipball/abc123');

    $specific = $metadata->findVersion('v10.0.0');

    expect($specific)
        ->toBeInstanceOf(PackageVersion::class)
        ->and($specific->version)->toBe('v10.0.0')
        ->and($specific->downloadUrl())->toBe('https://api.github.com/repos/laravel/framework/zipball/def456');
});

it('handles an empty p2 response', function () {
    $mockClient = new MockClient([
        GetPackageMetadataRequest::class => MockResponse::make(['packages' => []]),
    ]);

    $connector = new Composer;
    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetPackageMetadataRequest('unknown/package'));
    $metadata = $response->dtoOrFail();

    expect($metadata)
        ->toBeInstanceOf(PackageMetadata::class)
        ->and($metadata->packageName)->toBe('')
        ->and($metadata->versions)->toBeEmpty();
});
