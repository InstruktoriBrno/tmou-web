<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Components\EventQualificationPuzzlesStatisticsControl;

use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Qualification\FindPuzzleStatisticsService;

class EventQualificationPuzzlesStatisticsControl extends Control
{
    private FindPuzzleStatisticsService $findPuzzlesOfEventService;

    public function __construct(
        FindPuzzleStatisticsService $findPuzzlesOfEventService
    ) {
        parent::__construct();
        $this->findPuzzlesOfEventService = $findPuzzlesOfEventService;
    }

    public function render(): void
    {
        parent::render();
    }

    public function renderForEvent(Event $event): void
    {
        $this->template->event = $event;
        $this->template->statistics = ($this->findPuzzlesOfEventService)($event);
        $this->render();
    }
}
