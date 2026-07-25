<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Components\EventQualificationResultsControl;

use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Qualification\FindLevelsService;
use InstruktoriBrno\TMOU\Services\Qualification\FindPuzzlesOfEventService;
use InstruktoriBrno\TMOU\Services\Qualification\FindResultsService;
use InstruktoriBrno\TMOU\Services\Qualification\FindTeamResultsByLevelsService;

class EventQualificationResultsControl extends Control
{

    private FindResultsService $findResultsService;

    private FindLevelsService $findLevelsService;
    private FindPuzzlesOfEventService $findPuzzlesOfEventService;

    private FindTeamResultsByLevelsService $findTeamResultsByLevelsService;

    public function __construct(
        FindResultsService $findResultsService,
        FindLevelsService $findLevelsService,
        FindPuzzlesOfEventService $findPuzzlesOfEventService,
        FindTeamResultsByLevelsService $findTeamResultsByLevelsService
    ) {
        $this->findResultsService = $findResultsService;
        $this->findLevelsService = $findLevelsService;
        $this->findPuzzlesOfEventService = $findPuzzlesOfEventService;
        $this->findTeamResultsByLevelsService = $findTeamResultsByLevelsService;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderForEvent(Event $event): void
    {
        $teams = ($this->findResultsService)($event);
        $this->template->event = $event;
        $this->template->teams = $teams;
        // Teams are ordered by position ASC, so the last qualified row encountered has the highest position among qualified teams.
        // Computed explicitly (instead of relying on the qualified flag changing between adjacent rows) because teams that
        // opted for qualification only can be interleaved among truly qualified teams while never being qualified themselves.
        $this->template->lastQualifiedPosition = $this->findLastQualifiedPosition($teams);
        $this->template->levels = ($this->findLevelsService)($event);
        $this->template->puzzles = ($this->findPuzzlesOfEventService)($event);
        $this->template->answersByLevelAndTeam = ($this->findTeamResultsByLevelsService)($event);
        $this->render();
    }

    /**
     * @param array<mixed> $teams
     */
    private function findLastQualifiedPosition(array $teams): int
    {
        $lastQualifiedPosition = 0;
        foreach ($teams as $team) {
            if ((bool) $team['qualified']) {
                $lastQualifiedPosition = (int) $team['position'];
            }
        }
        return $lastQualifiedPosition;
    }
}
