<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Mailgun;

use Nette\Mail\Message;
use Mailgun\Mailgun;

class MailgunSender
{
    /** @var mailgunClient */
    private $mailgunClient;
    /** @var netteMailMessage */
    private $netteMailMessage;

    public function __construct(string $apiKey, string $apiBaseUrl)
    {
        $this->mailgunClient = Mailgun::create($apiKey, $apiBaseUrl);
    }

    public function setNetteMailMessage(Nette\Mail\Message $netteMailMessage)
    {
        $this->netteMailMessage = netteMailMessage;
    }    
}
