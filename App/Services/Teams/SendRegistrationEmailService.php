<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Bridges\Latte\TemplateFactory;
use InstruktoriBrno\TMOU\Model\Team;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Tracy\Debugger;
use Tracy\ILogger;
use function htmlspecialchars;

class SendRegistrationEmailService
{
    /** @var IMailer */
    private $mailer;

    /** @var string */
    private $mailFromNoReply;

    /** @var string */
    private $mailReplyTo;

    /** @var TemplateFactory */
    private $templateFactory;

    public function __construct(string $mailFromNoReply, string $mailReplyTo, IMailer $mailer, TemplateFactory $templateFactory)
    {
        $this->mailFromNoReply = $mailFromNoReply;
        $this->mailReplyTo = $mailReplyTo;
        $this->mailer = $mailer;
        $this->templateFactory = $templateFactory;
    }

    public function __invoke(Team $team): void
    {
        $message = new Message();
        $message->setFrom($this->mailFromNoReply, 'TMOU');
        $message->addReplyTo($this->mailReplyTo, 'TMOU');
        $message->addTo($team->getEmail(), $team->getName());
        $message->setSubject($subject = sprintf('[TMOU %s] Registrace týmu %s', $team->getEvent()->getNumber(), $team->getName()));

        $template = $this->templateFactory->createTemplate();
        $template->setFile(__DIR__ . '/Templates/registrationEmail.latte');
        $content = sprintf("Váš tým %s\nbyl úspěšně zaregistrován do %s. ročníku TMOU.\n\n-- Vaši organizátoři", htmlspecialchars($team->getName()), $team->getEvent()->getNumber());
        $template->setParameters(['content' => $content, 'subject' => $subject]);

        $message->setHtmlBody($template->renderToString());

        try {
            $this->mailer->send($message);
        } catch (\Nette\Mail\SendException $sendException) {
            Debugger::log($sendException, ILogger::EXCEPTION);
        }
    }
}
