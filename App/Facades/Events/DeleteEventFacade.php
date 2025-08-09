<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Events;

use InstruktoriBrno\TMOU\Services\Events\DeleteEventService;

class DeleteEventFacade
{
    private DeleteEventService $deleteEventService;

    public function __construct(
        DeleteEventService $deleteEventService
    ) {
        $this->deleteEventService = $deleteEventService;
    }

    /**
     * Takes care about complete deletion of event with given ID
     *
     * @param int $eventId
     * @throws \InstruktoriBrno\TMOU\Facades\Events\Exceptions\EventDeleteFailedException
     */
    public function __invoke(int $eventId): void
    {
        try {
            ($this->deleteEventService)($eventId);
        } catch (\InstruktoriBrno\TMOU\Services\Events\Exceptions\EventDeleteFailedException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Events\Exceptions\EventDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
