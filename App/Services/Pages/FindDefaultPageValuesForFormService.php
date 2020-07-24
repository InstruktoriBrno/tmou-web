<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;

class FindDefaultPageValuesForFormService
{

    /** @var FindEventByNumberService */
    private $findEventByNumberService;

    public function __construct(FindEventByNumberService $findEventByNumberService)
    {
        $this->findEventByNumberService = $findEventByNumberService;
    }

    /**
     * Returns defaults (typical values) for new page
     *
     * @param int|null $eventNumber
     *
     * @return array<string, int>
     */
    public function __invoke(?int $eventNumber): array
    {
        if ($eventNumber === null) {
            return [];
        }
        $event = ($this->findEventByNumberService)($eventNumber);
        if ($event === null) {
            throw new \InstruktoriBrno\TMOU\Services\Pages\Exceptions\NoSuchEventException;
        }
        return ['event' => $event->getId()];
    }
}
