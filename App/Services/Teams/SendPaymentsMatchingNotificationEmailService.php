<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Bridges\Latte\TemplateFactory;
use InstruktoriBrno\TMOU\Model\Event;
use Nette\Application\LinkGenerator;
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Tracy\Debugger;
use Tracy\ILogger;

class SendPaymentsMatchingNotificationEmailService
{
    private IMailer $mailer;

    private string $mailFromNoReply;

    private string $notificationEmail;

    private TemplateFactory $templateFactory;

    private LinkGenerator $linkGenerator;

    public function __construct(string $mailFromNoReply, string $notificationEmail, IMailer $mailer, TemplateFactory $templateFactory, LinkGenerator $linkGenerator)
    {
        $this->mailFromNoReply = $mailFromNoReply;
        $this->notificationEmail = $notificationEmail;
        $this->mailer = $mailer;
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
    }

    public function __invoke(Event $event): void
    {
        $message = new Message();
        $message->setFrom($this->mailFromNoReply, 'TMOU');
        $message->addTo($this->notificationEmail, 'TMOU');
        $message->setSubject($subject = sprintf('[TMOU %s] Párování plateb vyžaduje pozornost', $event->getNumber()));

        $template = $this->templateFactory->createTemplate();
        if (!$template instanceof DefaultTemplate) {
            throw new \InstruktoriBrno\TMOU\Exceptions\LogicException('Template is not a DefaultTemplate from Latte.');
        }
        $template->setFile(__DIR__ . '/Templates/paymentsMatchingEmail.latte');
        $link = $this->linkGenerator->link('Teams:payments');
        $content = sprintf("Zdravím,\npárování plateb v %s. ročníku TMOU vyžaduje vaši pozornost.\nPřejít na <a href='{$link}'>Log párování plateb</a>\n\n-- TMOU web", $event->getNumber());
        $template->setParameters(['content' => $content, 'subject' => $subject]);

        $message->setHtmlBody($template->renderToString());

        try {
            $this->mailer->send($message);
        } catch (\Nette\Mail\SendException $sendException) {
            Debugger::log($sendException, ILogger::EXCEPTION);
        }
    }
}
