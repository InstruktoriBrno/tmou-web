<?php declare(strict_types=1);

namespace InstruktoriBrno\TMOU\Utils;

use Mailgun\Exception\HydrationException;
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

        if (0 !== strpos($contentType, 'application/json') && 0 !== strpos($contentType, 'application/octet-stream')) {
            throw new HydrationException('The ModelHydrator cannot hydrate response with Content-Type: '.$contentType);
        }

        $data = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new HydrationException(sprintf('Error (%d) when trying to json_decode response', json_last_error()));
        }

        return MailGunCustomSendResponse::create($data, $response);
    }
}
