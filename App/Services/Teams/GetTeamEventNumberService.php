<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\Common\Persistence\ObjectRepository; // phpcs:ignore
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class GetTeamEventNumberService
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

    public function __invoke(Event $event): int
    {
        $items = $this->teamRepository->findBy(['event' => $event], ['number' => 'DESC'], 1);
        if (count($items) === 0) {
            return 1;
        }
        /** @var Team $entity */
        $entity = $items[0];
        return $entity->getNumber() + 1;
    }
}
