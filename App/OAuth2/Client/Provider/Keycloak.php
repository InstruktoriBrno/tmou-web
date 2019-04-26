<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\OAuth2\Client\Provider;

use GuzzleHttp\Client;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak as KeycloakLibrary;

class Keycloak extends KeycloakLibrary
{
    public function __construct(array $options = [], array $collaborators = [], bool $debugMode = false)
    {
        parent::__construct($options, $collaborators);
        // In development mode disable SSL certificate verification (due to usage of self-signed)
        if ($debugMode) {
            $httpClient = $this->getHttpClient();
            $options = $httpClient->getConfig();
            $options['verify'] = false;
            $newHttpClient = new Client($options);
            $this->setHttpClient($newHttpClient);
        }
    }
}
