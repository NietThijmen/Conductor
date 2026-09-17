<?php

namespace App\Http\Integrations\Composer;

use App\Enums\RegistryAuthType;
use App\Models\Registry;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

/**
 * The Packagist P2 API connector.
 *
 * @see https://packagist.org/apidoc
 */
class Composer extends Connector
{
    use AcceptsJson;

    public function __construct(
        public readonly ?Registry $registry = null
    ) {}

    /**
     * Resolve the base URL for the registry, falling back to the public Packagist repository.
     */
    public function resolveBaseUrl(): string
    {
        return $this->registry === null
            ? 'https://repo.packagist.org'
            : $this->registry->url;
    }

    /**
     * Configure authentication based on the registry's settings.
     */
    protected function defaultAuth(): BasicAuthenticator|HeaderAuthenticator|TokenAuthenticator|null
    {
        if ($this->registry === null) {
            return null;
        }

        $config = $this->registry->auth_config ?? [];

        return match ($this->registry->auth_type) {
            RegistryAuthType::Basic => new BasicAuthenticator(
                username: (string) ($config['username'] ?? ''),
                password: (string) ($config['password'] ?? '')
            ),
            RegistryAuthType::Token => new TokenAuthenticator(
                token: (string) ($config['token'] ?? '')
            ),
            RegistryAuthType::Composer => new HeaderAuthenticator(
                accessToken: 'Basic '.base64_encode(json_encode($config, JSON_THROW_ON_ERROR)),
                headerName: 'COMPOSER-AUTH'
            ),
            default => null,
        };
    }

    /**
     * Default headers for every request.
     */
    protected function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Default HTTP client options.
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
