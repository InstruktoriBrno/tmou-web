<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamByNameService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given team with given name (in given event) or null when no such exists
     *
     * @param Event $event
     * @param string $name
     *
     * @return Team|null
     */
    public function __invoke(Event $event, string $name): ?Team
    {
        return $this->entityManager->getRepository(Team::class)->findOneBy(['event' => $event, 'name' => $name]);
    }
}
