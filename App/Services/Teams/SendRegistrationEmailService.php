<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Model\Team;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Tracy\Debugger;
use Tracy\ILogger;

class SendRegistrationEmailService
{
    /** @var IMailer */
    private $mailer;

    /** @var string */
    private $mailFromNoReply;

    /** @var string */
    private $mailReplyTo;

    public function __construct(string $mailFromNoReply, string $mailReplyTo, IMailer $mailer)
    {
        $this->mailFromNoReply = $mailFromNoReply;
        $this->mailReplyTo = $mailReplyTo;
        $this->mailer = $mailer;
    }

    public function __invoke(Team $team): void
    {
        $message = new Message();
        $message->setFrom($this->mailFromNoReply, 'TMOU');
        $message->addReplyTo($this->mailReplyTo, 'TMOU');
        $message->addTo($team->getEmail(), $team->getName());
        $message->setSubject(sprintf('[TMOU %s] Registrace týmu %s', $team->getEvent()->getNumber(), $team->getName()));
        $message->setBody(sprintf("Váš tým %s\nbyl úspěšně zaregistrován do %s. ročníku TMOU.\n\n-- Vaši organizátoři", $team->getName(), $team->getEvent()->getNumber()));

        try {
            $this->mailer->send($message);
        } catch (\Nette\Mail\SendException $sendException) {
            Debugger::log($sendException, ILogger::EXCEPTION);
        }
    }
}
