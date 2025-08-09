<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\Persistence\ObjectRepository; // phpcs:ignore
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamsInEventService
{
    private EntityManagerInterface $entityManager;

    /** @var ObjectRepository<Team>|EntityRepository<Team> */
    private $teamRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $this->entityManager->getRepository(Team::class);
    }

    /**
     * Returns all teams registered on given event
     * Note: this finds all teams of given event (not just in state registered)
     *
     * @param Event $event
     * @return Team[]
     */
    public function findRegisteredTeams(Event $event): array
    {
        return $this->teamRepository->findBy(['event' => $event], ['name' => 'ASC']);
    }

    /**
     * Returns all teams qualified on given event
     * Note: this finds all teams in states qualified or playing on given event (not just in state playing)
     *
     * @param Event $event
     * @return Team[]
     */
    public function findQualifiedTeams(Event $event): array
    {
        return $this->teamRepository->findBy(['event' => $event, 'gameStatus' => [GameStatus::QUALIFIED(), GameStatus::PLAYING()]], ['name' => 'ASC']);
    }

    /**
     * Returns all teams playing on given event
     *
     * @param Event $event
     * @return Team[]
     */
    public function findPlayingTeams(Event $event): array
    {
        return $this->teamRepository->findBy(['event' => $event, 'gameStatus' => GameStatus::PLAYING()], ['name' => 'ASC']);
    }
}
