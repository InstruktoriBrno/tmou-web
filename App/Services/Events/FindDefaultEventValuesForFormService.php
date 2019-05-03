<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

class FindDefaultEventValuesForFormService
{
    /** @var FindLatestEventService */
    private $findLatestEventService;

    public function __construct(FindLatestEventService $findLatestEventService)
    {
        $this->findLatestEventService = $findLatestEventService;
    }

    /**
     * Returns defaults (typical values) for new event
     * @return array
     */
    public function __invoke(): array
    {
        $latestEvent = ($this->findLatestEventService)();
        return [
            'number' => $latestEvent !== null ? $latestEvent->getNumber() + 1 : 1,
            'hasQualification' => true,
            'qualifiedTeamCount' => 225,
            'totalTeamCount' => 250,
        ];
    }
}
