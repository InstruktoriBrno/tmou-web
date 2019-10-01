<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamsPairsInEventService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ObjectRepository|EntityRepository */
    private $teamRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $this->entityManager->getRepository(Team::class);
    }

    /**
     * Returns all teams of given event as id => name (id, number, state)
     *
     * @param Event $event
     * @return array
     */
    public function __invoke(Event $event): array
    {
        /** @var Team[] $teams */
        $teams = $this->teamRepository->findBy(['event' => $event], ['name' => 'ASC']);
        $output = [];
        foreach ($teams as $team) {
            $output[$team->getId()] = sprintf('%s (ID %d, N %d, %s)', $team->getName(), $team->getId(), $team->getNumber(), $team->getGameStatus()->toScalar());
        }
        return $output;
    }
}
