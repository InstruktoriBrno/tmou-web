<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\System;

use InstruktoriBrno\TMOU\Utils\MailGunCustomHydrator;
use InstruktoriBrno\TMOU\Utils\MailGunCustomSendResponse;
use Mailgun\HttpClient\HttpClientConfigurator;
use Mailgun\Model\Message\SendResponse;
use Nette\Mail\Message;
use Mailgun\Mailgun;
use Psr\Http\Message\ResponseInterface;
use Tracy\Debugger;
use Tracy\ILogger;

class MailgunSenderService
{
    /** @var Mailgun */
    private $mailgunClient;

    /** @var string */
    private $domain;

    public function __construct(string $apiKey, string $apiBaseUrl, string $domain)
    {
        // Custom mailgun client creation as we need access to the HTTP response
        // $this->mailgunClient = Mailgun::create($apiKey, $apiBaseUrl);
        $httpClientConfigurator = (new HttpClientConfigurator())
            ->setApiKey($apiKey)
            ->setEndpoint($apiBaseUrl);

        $this->mailgunClient = new Mailgun($httpClientConfigurator, new MailGunCustomHydrator());

        $this->domain = $domain;
    }

    /**
     * Sends message via MailGun
     *
     * @param Message $message
     * @return bool
     * @throws \InstruktoriBrno\TMOU\Services\System\Exceptions\ReachedLimitException when limit of recipients or rate is reached
     */
    public function sendNetteMessage(Message $message): bool
    {
        /** @var MailGunCustomSendResponse|ResponseInterface|SendResponse $response */
        $response = $this->mailgunClient->messages()->send($this->domain, [
            'from'    => $message->getEncodedHeader('From'),
            'to'      => $message->getEncodedHeader('To'),
            'cc'      => $message->getEncodedHeader('Cc'),
            'bcc'     => $message->getEncodedHeader('Bcc'),
            'subject' => $message->getSubject(),
            'text'    => $message->getBody(),
            'html'    => $message->getHtmlBody(),
        ]);
        if (!$response instanceof MailGunCustomSendResponse) {
            Debugger::log($response, ILogger::ERROR);
            throw new \InstruktoriBrno\TMOU\Services\System\Exceptions\UnexpectedResponseException();
        }

        $remainingRecipientLimit = $response->getResponse()->getHeader('X-Recipient-Remaining');
        $remainingRateLimit = $response->getResponse()->getHeader('X-Ratelimit-Remaining');
        if ((isset($remainingRecipientLimit[0]) && (int) $remainingRecipientLimit[0] === 0) ||
            (isset($remainingRateLimit[0]) && (int) $remainingRateLimit[0] === 0)
        ) {
            throw new \InstruktoriBrno\TMOU\Services\System\Exceptions\ReachedLimitException();
        }

        if ($response->getResponse()->getStatusCode() === 200) {
            return true;
        }
        Debugger::log($response, ILogger::ERROR);
        return false;
    }
}
