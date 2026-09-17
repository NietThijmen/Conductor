<?php

namespace App\Http\Integrations\Composer\Requests;

use App\Http\Integrations\Composer\Data\PackageMetadata;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Request metadata for a Composer package from the Packagist P2 API.
 *
 * @see https://packagist.org/apidoc#get-package-data
 */
final class GetPackageMetadataRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        public readonly string $package,
        public readonly bool $includeDev = false,
    ) {}

    /**
     * Resolve the endpoint for the P2 metadata request.
     */
    public function resolveEndpoint(): string
    {
        $suffix = $this->includeDev ? '~dev' : '';

        return "/p2/{$this->package}{$suffix}.json";
    }

    /**
     * Cast the JSON response into a typed metadata DTO.
     */
    public function createDtoFromResponse(Response $response): PackageMetadata
    {
        return PackageMetadata::fromArray($response->array());
    }
}
