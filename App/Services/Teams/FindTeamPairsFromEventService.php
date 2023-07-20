<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamPairsFromEventService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns id - name pairs of all teams in given event
     *
     * @param Event $event
     *
     * @return array<int, string>
     */
    public function __invoke(Event $event): array
    {
        $teams = $this->entityManager->getRepository(Team::class)->findBy(['event' => $event]);
        $output = [];
        foreach ($teams as $team) {
            $output[$team->getId()] = $team->getName();
        }
        return $output;
    }
}
