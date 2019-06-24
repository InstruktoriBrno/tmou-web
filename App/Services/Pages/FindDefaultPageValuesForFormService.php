<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

class FindDefaultPageValuesForFormService
{

    /**
     * Returns defaults (typical values) for new page
     *
     * @param int|null $eventId
     *
     * @return array
     */
    public function __invoke(?int $eventId): array
    {
        return ['event' => $eventId];
    }
}
