<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Services\Qualification\FindTeamAnswersService;
use InstruktoriBrno\TMOU\Services\Qualification\UpdateEventQualificationScoreboardService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamPairsFromEventService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;

class DeleteQualificationProgressFacade
{
    private EntityManagerInterface $entityManager;

    private FindTeamService $findTeamService;

    private FindTeamAnswersService $findTeamAnswersService;

    private FindTeamPairsFromEventService $findTeamPairsFromEventService;

    private UpdateEventQualificationScoreboardService $updateEventQualificationScoreboardService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindTeamService $findTeamService,
        FindTeamAnswersService $findTeamAnswersService,
        FindTeamPairsFromEventService $findTeamPairsFromEventService,
        UpdateEventQualificationScoreboardService $updateEventQualificationScoreboardService
    ) {
        $this->entityManager = $entityManager;
        $this->findTeamService = $findTeamService;
        $this->findTeamAnswersService = $findTeamAnswersService;
        $this->findTeamPairsFromEventService = $findTeamPairsFromEventService;
        $this->updateEventQualificationScoreboardService = $updateEventQualificationScoreboardService;
    }

    /**
     * Delete qualification progress either for given team or for all teams in given event
     *
     * @param Event $event
     * @param int|null $teamId
     *
     */
    public function __invoke(Event $event, ?int $teamId): void
    {
        $team = null;
        if ($teamId !== null) {
            $team = ($this->findTeamService)($teamId);
            if ($team === null || $team->getEvent()->getId() !== $event->getId()) {
                throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\NoSuchTeamInEventException();
            }
        }

        if ($team !== null) {
            $this->resetTeam($team);
            $this->entityManager->wrapInTransaction(function () use ($team): void {
                ($this->updateEventQualificationScoreboardService)($team->getEvent());
            });
            return;
        }
        $allEventTeams = ($this->findTeamPairsFromEventService)($event);
        foreach ($allEventTeams as $id => $name) {
            $team = ($this->findTeamService)($id);
            if ($team === null) {
                continue;
            }
            $this->resetTeam($team);
        }
        $this->entityManager->wrapInTransaction(function () use ($team): void {
            ($this->updateEventQualificationScoreboardService)($team->getEvent());
        });
    }

    private function resetTeam(Team $team): void
    {
        $team->resetQualification();
        $this->entityManager->persist($team);
        $answers = ($this->findTeamAnswersService)($team);
        foreach ($answers as $answer) {
            $this->entityManager->remove($answer);
        }
        $this->entityManager->flush();
    }
}
