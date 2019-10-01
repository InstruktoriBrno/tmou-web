<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use InstruktoriBrno\TMOU\Bridges\Latte\TemplateFactory;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Services\Events\EventMacroDataProvider;
use InstruktoriBrno\TMOU\Services\System\MailgunSenderService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsForMailingInEventService;
use InstruktoriBrno\TMOU\Services\Teams\TeamMacroDataProvider;
use Nette\Mail\Message;
use Nette\Utils\ArrayHash;

class BatchMailTeamsFacade
{
    const PREVIEW_LIMIT = 10;

    /** @var FindTeamsForMailingInEventService */
    private $findTeamsForMailingInEventService;

    /** @var FindTeamService */
    private $findTeamService;

    /** @var EventMacroDataProvider */
    private $eventMacroDataProvider;

    /** @var TeamMacroDataProvider */
    private $teamMacroDataProvider;

    /** @var string */
    private $emailFrom;

    /** @var TemplateFactory */
    private $templateFactory;

    /** @var MailgunSenderService */
    private $mailgunSenderService;

    public function __construct(
        string $emailFrom,
        FindTeamsForMailingInEventService $findTeamsForMailingInEventService,
        FindTeamService $findTeamService,
        EventMacroDataProvider $eventMacroDataProvider,
        TeamMacroDataProvider $teamMacroDataProvider,
        TemplateFactory $templateFactory,
        MailgunSenderService $mailgunSenderService
    ) {
        $this->findTeamsForMailingInEventService = $findTeamsForMailingInEventService;
        $this->findTeamService = $findTeamService;
        $this->eventMacroDataProvider = $eventMacroDataProvider;
        $this->teamMacroDataProvider = $teamMacroDataProvider;
        $this->emailFrom = $emailFrom;
        $this->templateFactory = $templateFactory;
        $this->mailgunSenderService = $mailgunSenderService;
    }

    /**
     * @param ArrayHash $values
     * @param Event $event
     * @param bool $previewOnly
     * @return array
     */
    public function __invoke(ArrayHash $values, Event $event, bool $previewOnly): array
    {
        // Prepare the batch
        $states = $values->states;
        $teams = $values->teams;

        $batch = [];
        if (in_array("", $states, true)) {
            $batch += $this->findTeamsForMailingInEventService->findAllTeams($event);
        } elseif (in_array(GameStatus::REGISTERED()->toScalar(), $states, true)) {
            $batch += $this->findTeamsForMailingInEventService->findTeamsInGameState($event, GameStatus::REGISTERED());
        } elseif (in_array(GameStatus::QUALIFIED()->toScalar(), $states, true)) {
            $batch += $this->findTeamsForMailingInEventService->findTeamsInGameState($event, GameStatus::QUALIFIED());
        } elseif (in_array(GameStatus::NOT_QUALIFIED()->toScalar(), $states, true)) {
            $batch += $this->findTeamsForMailingInEventService->findTeamsInGameState($event, GameStatus::NOT_QUALIFIED());
        } elseif (in_array(GameStatus::PLAYING()->toScalar(), $states, true)) {
            $batch += $this->findTeamsForMailingInEventService->findTeamsInGameState($event, GameStatus::PLAYING());
        }

        /** @var int $team */
        foreach ($teams as $team) {
            if (!array_key_exists($team, $batch)) {
                $batch[$team] = ($this->findTeamService)($team);
            }
        }

        // Send the batch
        $previews = [];
        $sent = 0;
        $failed = 0;
        $this->eventMacroDataProvider->setEvent($event);
        $content = $values->content;
        $subject = $values->subject;
        /** @var Team $team */
        foreach ($batch as $team) {
            if (!$team instanceof Team) {
                continue;
            }
            $this->teamMacroDataProvider->setTeam($team);
            $message = new Message();
            $message->setSubject($subject);
            $message->setFrom($this->emailFrom);
            $message->addTo($team->getEmail());

            $template = $this->templateFactory->createTemplate();
            $template->setFile(__DIR__ . '/Templates/batchEmail.latte');
            $template->setParameters(['content' => $content, 'subject' => $subject]);
            $message->setHtmlBody($template->renderToString());

            if ($previewOnly) {
                $previews[] = $template->renderToString();
                if (($sent + $failed) > self::PREVIEW_LIMIT) {
                    throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\PreviewException($previews);
                }
                continue;
            }

            $result = $this->mailgunSenderService->sendNetteMessage($message);
            if ($result) {
                $sent += 1;
            } else {
                $failed += 1;
            }
        }
        if ($previewOnly) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\PreviewException($previews);
        }
        return [$sent, $failed];
    }
}
