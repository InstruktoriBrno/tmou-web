<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Mailgun\Hydrator\Hydrator;
use Psr\Http\Message\ResponseInterface;

final class MailGunCustomHydrator implements Hydrator
{
    /**
     * @param ResponseInterface $response
     * @param string $class
     * @return MailGunCustomSendResponse
     */
    public function hydrate(ResponseInterface $response, string $class)
    {
        $body = $response->getBody()->__toString();
        $contentType = $response->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/json') !== 0 && strpos($contentType, 'application/octet-stream') !== 0) {
            throw new \Mailgun\Exception\HydrationException('The ModelHydrator cannot hydrate response with Content-Type: '.$contentType);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Mailgun\Exception\HydrationException(sprintf('Error (%d) when trying to json_decode response', json_last_error()));
        }

        return MailGunCustomSendResponse::create($data, $response);
    }
}
