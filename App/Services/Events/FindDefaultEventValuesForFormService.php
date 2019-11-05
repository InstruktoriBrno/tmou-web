<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use DateTimeImmutable;

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
            'qualificationStart' => (new DateTimeImmutable('last saturday of September'))->setTime(18, 0, 0),
            'qualificationEnd' => (new DateTimeImmutable('last saturday of September'))->setTime(22, 0, 0),
            'registrationDeadline' => (new DateTimeImmutable('last saturday of September'))->setTime(22, 0, 0),
            'eventStart' => (new DateTimeImmutable('first friday of November'))->setTime(17, 0, 0),
            'eventEnd' => (new DateTimeImmutable('first friday of November'))->modify('+37 hours'),
            'changeDeadline' => (new DateTimeImmutable('first friday of November'))->modify('-5 days')->setTime(23, 59, 59),
            'qualifiedTeamCount' => 225,
            'totalTeamCount' => 250,
            'amount' => 800,
            'paymentDeadline' => (new DateTimeImmutable('last day of October')),
        ];
    }
}
