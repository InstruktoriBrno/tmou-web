<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\OAuth2\Client\Provider;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak as KeycloakLibrary;

class Keycloak extends KeycloakLibrary
{
    private bool $debugMode;

    // @phpstan-ignore-next-line
    public function __construct(array $options = [], array $collaborators = [], bool $debugMode = false)
    {
        parent::__construct($options, $collaborators);
        $this->debugMode = $debugMode;
        // In development mode disable SSL certificate verification (due to usage of self-signed)
        if ($debugMode) {
            $httpClient = $this->getHttpClient();
            $options = $httpClient->getConfig();
            $options['verify'] = false;
            $newHttpClient = new Client($options);
            $this->setHttpClient($newHttpClient);
        }
    }

    public function getParsedResponse(RequestInterface $request)
    {
        // When inside of local development environment, we need to use container name to directly connect to Keycloak
        if ($this->debugMode) {
            $uri = $request->getUri();
            $uri = $uri->withHost('keycloak')
                ->withPort(8443)
                ->withScheme('https');
            $request = $request->withUri($uri);
        }
        return parent::getParsedResponse($request);
    }
}
