<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\Common\Persistence\ObjectRepository; // phpcs:ignore
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamsForMailingInEventService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ObjectRepository<Team>|EntityRepository */
    private $teamRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $this->entityManager->getRepository(Team::class);
    }

    /**
     * Returns all teams on given event
     *
     * @param Event $event
     * @return Team[]
     */
    public function findAllTeams(Event $event): array
    {
        /** @var Team[] $teams */
        $teams = $this->teamRepository->findBy(['event' => $event]);
        $output = [];
        foreach ($teams as $team) {
            $output[$team->getId()] = $team;
        }
        return $output;
    }

    /**
     * Returns all teams in given state on given event
     *
     * @param Event $event
     * @param GameStatus $status
     * @return Team[]
     */
    public function findTeamsInGameState(Event $event, GameStatus $status): array
    {
        /** @var Team[] $teams */
        $teams = $this->teamRepository->findBy(['event' => $event, 'gameStatus' => $status]);
        $output = [];
        foreach ($teams as $team) {
            $output[$team->getId()] = $team;
        }
        return $output;
    }
}
