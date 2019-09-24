<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\VO\PasswordResetTokenVO;
use Nette\Application\LinkGenerator;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Tracy\Debugger;
use Tracy\ILogger;

class SendResetPasswordEmailService
{
    /** @var IMailer */
    private $mailer;

    /** @var string */
    private $mailFromNoReply;

    /** @var string */
    private $mailReplyTo;

    /** @var LinkGenerator */
    private $linkGenerator;

    public function __construct(string $mailFromNoReply, string $mailReplyTo, IMailer $mailer, LinkGenerator $linkGenerator)
    {
        $this->mailFromNoReply = $mailFromNoReply;
        $this->mailReplyTo = $mailReplyTo;
        $this->mailer = $mailer;
        $this->linkGenerator = $linkGenerator;
    }

    public function __invoke(Team $team, PasswordResetTokenVO $token): void
    {
        $message = new Message();
        $message->setFrom($this->mailFromNoReply, 'TMOU');
        $message->addReplyTo($this->mailReplyTo, 'TMOU');
        $message->addTo($team->getEmail(), $team->getName());
        $message->setSubject(sprintf('[TMOU %s] Žádost o nové heslo týmu %s', $team->getEvent()->getNumber(), $team->getName()));
        $message->setBody(sprintf(
            "Zdravíme,\nněkdo požádal u týmu %s v %s. ročníku TMOU o nové heslo.\n\n
            Pro nastavení nového hesla přejděte na stránku %s a zadejte kromě e-mailu též kód %s. 
            Tento kód je platný do %s, poté bude potřeba žádost opakovat.\n\n-- Vaši organizátoři",
            $team->getName(),
            $team->getEvent()->getNumber(),
            $this->linkGenerator->link('Pages:resetPassword', [$team->getEvent()->getNumber()]),
            $token->getToken(),
            $token->getExpiration()->format('j. n. Y H:i:s')
        ));

        try {
            $this->mailer->send($message);
        } catch (\Nette\Mail\SendException $sendException) {
            Debugger::log($sendException, ILogger::EXCEPTION);
        }
    }
}
