<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Components\EventQualificationResultsControl;

use InstruktoriBrno\TMOU\Services\Qualification\FindLevelsService;
use InstruktoriBrno\TMOU\Services\Qualification\FindPuzzlesOfEventService;
use InstruktoriBrno\TMOU\Services\Qualification\FindResultsService;
use InstruktoriBrno\TMOU\Services\Qualification\FindTeamResultsByLevelsService;

class EventQualificationResultsControlFactory
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
    public function create(): EventQualificationResultsControl
    {
        return new EventQualificationResultsControl(
            $this->findResultsService,
            $this->findLevelsService,
            $this->findPuzzlesOfEventService,
            $this->findTeamResultsByLevelsService,
        );
    }
}
