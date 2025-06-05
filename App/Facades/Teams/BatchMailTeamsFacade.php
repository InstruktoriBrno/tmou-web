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
use Nette\Bridges\ApplicationLatte\DefaultTemplate;
use Nette\Mail\Message;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

class BatchMailTeamsFacade
{
    private const PREVIEW_LIMIT = 10;

    private FindTeamsForMailingInEventService $findTeamsForMailingInEventService;

    private FindTeamService $findTeamService;

    private EventMacroDataProvider $eventMacroDataProvider;

    private TeamMacroDataProvider $teamMacroDataProvider;

    private string $emailFrom;

    private TemplateFactory $templateFactory;

    private MailgunSenderService $mailgunSenderService;

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
     * @return int
     */
    public function __invoke(ArrayHash $values, Event $event, bool $previewOnly): int
    {
        // Prepare the batch
        $states = $values->states;
        $teams = $values->teams;
        $skip = (int) $values->skip;

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

        // Sort teams by their alphabetical name, to make sending deterministic
        uasort($batch, function (?Team $team1, ?Team $team2): int {
            return $team1?->getName() <=> $team2?->getName();
        });

        // Send the batch
        $previews = [];
        $sent = 0;
        $this->eventMacroDataProvider->setEvent($event);
        $content = $values->content;
        $subject = $values->subject;
        /** @var Team|mixed $team */
        foreach ($batch as $team) {
            if (!$team instanceof Team) {
                continue;
            }
            if ($skip > 0) {
                $skip -= 1;
                continue;
            }
            $this->teamMacroDataProvider->setTeam($team);
            $message = new Message();
            $message->setSubject($subject);
            $message->setFrom($this->emailFrom);
            $message->addTo($team->getEmail());

            $template = $this->templateFactory->createTemplate();
            if (!$template instanceof DefaultTemplate) {
                throw new \InstruktoriBrno\TMOU\Exceptions\LogicException('Template is not a DefaultTemplate from Latte.');
            }
            $template->setFile(__DIR__ . '/Templates/batchEmail.latte');
            $template->setParameters(['content' => $content, 'subject' => $subject]);
            $message->setHtmlBody($template->renderToString());

            if ($previewOnly) {
                $previews[] = [
                    'teamId' => $team->getId(),
                    'teamNumber' => $team->getNumber(),
                    'teamName' => $team->getName(),
                    'subject' => $subject,
                    'body' => $template->renderToString(),
                ];
                if ($sent + 1 >= self::PREVIEW_LIMIT) {
                    throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\PreviewException($previews);
                }
                $sent += 1;
                continue;
            }

            try {
                $result = $this->mailgunSenderService->sendNetteMessage($message);
                if ($result) {
                    $sent += 1;
                } else {
                    throw new \Exception('Unexpected response, see the log.');
                }
            } catch (\InstruktoriBrno\TMOU\Services\System\Exceptions\ReachedLimitException $e) {
                throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\ReachedLimitException((string) ($sent + 1));
            } catch (\Exception $e) {
                Debugger::log($e, ILogger::EXCEPTION);
                throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\UnknownSendingException((string) $sent);
            }
        }
        if ($previewOnly) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\PreviewException($previews);
        }
        return $sent;
    }
}
