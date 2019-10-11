<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamByEventNumberService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given team with given team event number (in given event) or null when no such exists
     *
     * @param Event $event
     * @param int $number
     *
     * @return Team|null
     */
    public function __invoke(Event $event, int $number): ?Team
    {
        return $this->entityManager->getRepository(Team::class)->findOneBy(['event' => $event, 'number' => $number]);
    }
}
