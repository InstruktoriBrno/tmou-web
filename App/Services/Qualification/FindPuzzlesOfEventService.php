<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Puzzle;

class FindPuzzlesOfEventService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all puzzles grouped by level ID of particular event sorted by puzzle name
     *
     * @return array<int, array<int, Puzzle>>
     */
    public function __invoke(Event  $event): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->from(Puzzle::class, 'puzzle')
            ->join('puzzle.level', 'level')
            ->select('puzzle')
            ->where('level.event = :event')
            ->setParameter('event', $event)
            ->orderBy('puzzle.name', 'ASC');
        $data = $qb->getQuery()->getResult();
        $result = [];
        foreach ($data as $puzzle) {
            /** @var Puzzle $puzzle */
            $result[$puzzle->getLevel()->getId()][] = $puzzle;
        }
        return $result;
    }
}
