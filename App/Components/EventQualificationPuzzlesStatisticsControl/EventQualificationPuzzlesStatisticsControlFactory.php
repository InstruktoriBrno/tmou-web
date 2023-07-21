<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Components\EventQualificationPuzzlesStatisticsControl;

use InstruktoriBrno\TMOU\Services\Qualification\FindPuzzleStatisticsService;

class EventQualificationPuzzlesStatisticsControlFactory
{
    private FindPuzzleStatisticsService $findPuzzlesOfEventService;

    public function __construct(
        FindPuzzleStatisticsService $findPuzzlesOfEventService
    ) {
        $this->findPuzzlesOfEventService = $findPuzzlesOfEventService;
    }

    public function create(): EventQualificationPuzzlesStatisticsControl
    {
        return new EventQualificationPuzzlesStatisticsControl($this->findPuzzlesOfEventService);
    }
}
