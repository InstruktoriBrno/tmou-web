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
        parent::__construct();
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
        $this->template->event = $event;
        $this->template->teams = ($this->findResultsService)($event);
        $this->template->levels = ($this->findLevelsService)($event);
        $this->template->puzzles = ($this->findPuzzlesOfEventService)($event);
        $this->template->answersByLevelAndTeam = ($this->findTeamResultsByLevelsService)($event);
        $this->render();
    }
}
