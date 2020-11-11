<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Bridges\Latte\TemplateFactory;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\VO\PasswordResetTokenVO;
use Nette\Application\LinkGenerator;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Tracy\Debugger;
use Tracy\ILogger;
use function htmlspecialchars;

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

    /** @var TemplateFactory */
    private $templateFactory;

    public function __construct(string $mailFromNoReply, string $mailReplyTo, IMailer $mailer, LinkGenerator $linkGenerator, TemplateFactory $templateFactory)
    {
        $this->mailFromNoReply = $mailFromNoReply;
        $this->mailReplyTo = $mailReplyTo;
        $this->mailer = $mailer;
        $this->linkGenerator = $linkGenerator;
        $this->templateFactory = $templateFactory;
    }

    public function __invoke(Team $team, PasswordResetTokenVO $token): void
    {
        $message = new Message();
        $message->setFrom($this->mailFromNoReply, 'TMOU');
        $message->addReplyTo($this->mailReplyTo, 'TMOU');
        $message->addTo($team->getEmail(), $team->getName());
        $message->setSubject($subject = sprintf('[TMOU %s] Žádost o nové heslo týmu %s', $team->getEvent()->getNumber(), $team->getName()));
        $content = sprintf(
            "Zdravíme,<br>někdo požádal u týmu %s v %s. ročníku TMOU o nové heslo.<br><br>
            Pro nastavení nového hesla přejděte na stránku <a href=\"%s\">%s</a> a zadejte kromě e-mailu též kód <b>%s</b>.
            Tento kód je platný do %s, poté bude potřeba žádost opakovat.<br><br>-- Vaši organizátoři",
            htmlspecialchars(htmlspecialchars($team->getName())),
            $team->getEvent()->getNumber(),
            $this->linkGenerator->link('Pages:resetPassword', [$team->getEvent()->getNumber()]),
            $this->linkGenerator->link('Pages:resetPassword', [$team->getEvent()->getNumber()]),
            $token->getToken(),
            $token->getExpiration()->format('j. n. Y H:i:s')
        );
        $contentPlain = sprintf(
            "Zdravíme,\nněkdo požádal u týmu %s v %s. ročníku TMOU o nové heslo.\n\n
            Pro nastavení nového hesla přejděte na stránku %s<%s> a zadejte kromě e-mailu též kód %s.
            Tento kód je platný do %s, poté bude potřeba žádost opakovat.\n\n-- Vaši organizátoři",
            $team->getName(),
            $team->getEvent()->getNumber(),
            $this->linkGenerator->link('Pages:resetPassword', [$team->getEvent()->getNumber()]),
            $this->linkGenerator->link('Pages:resetPassword', [$team->getEvent()->getNumber()]),
            $token->getToken(),
            $token->getExpiration()->format('j. n. Y H:i:s')
        );

        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/Templates/resetPasswordEmail.latte');
        $template->setParameters(['content' => $content, 'subject' => $subject]);
        $message->setBody($contentPlain);
        $message->setHtmlBody($template->renderToString());

        try {
            $this->mailer->send($message);
        } catch (\Nette\Mail\SendException $sendException) {
            Debugger::log($sendException, ILogger::EXCEPTION);
        }
    }
}
