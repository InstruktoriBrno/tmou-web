<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use InstruktoriBrno\TMOU\Services\Teams\DeleteTeamService;

class DeleteTeamFacade
{
    private DeleteTeamService $deleteTeamService;

    public function __construct(
        DeleteTeamService $deleteEventService
    ) {
        $this->deleteTeamService = $deleteEventService;
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
            ($this->deleteTeamService)($eventId);
        } catch (\InstruktoriBrno\TMOU\Services\Teams\Exceptions\TeamDeleteFailedException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TeamDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
