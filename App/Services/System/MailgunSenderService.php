<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\System;

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
        $this->mailgunClient = Mailgun::create($apiKey, $apiBaseUrl);
        $this->domain = $domain;
    }

    /**
     * Sends message via mailgun
     *
     * @param Message $message
     * @return bool
     */
    public function sendNetteMessage(Message $message): bool
    {
        $response = $this->mailgunClient->messages()->send($this->domain, [
            'from'    => $message->getEncodedHeader('From'),
            'to'      => $message->getEncodedHeader('To'),
            'cc'      => $message->getEncodedHeader('Cc'),
            'bcc'     => $message->getEncodedHeader('Bcc'),
            'subject' => $message->getSubject(),
            'text'    => $message->getBody(),
            'html'    => $message->getHtmlBody(),
        ]);
        if ($response instanceof ResponseInterface && $response->getStatusCode() === 200) {
            return true;
        }
        Debugger::log($response, ILogger::ERROR);
        return false;
    }
}
