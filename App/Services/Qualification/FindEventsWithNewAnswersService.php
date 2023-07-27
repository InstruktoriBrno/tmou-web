<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Answer;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindEventsWithNewAnswersService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Return all events with new answers since given answer ID
     * @param int $latestProcessedAnswerId
     * @return Event[]
     */
    public function __invoke(int $latestProcessedAnswerId): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e');
        $qb->distinct();
        $qb->from(Answer::class, 'a')
            ->join(Team::class, 't', 'WITH', 't.id = a.team')
            ->join(Event::class, 'e', 'WITH', 'e.id = t.event');
        $qb->where('a.id > :answerId')
            ->setParameter('answerId', $latestProcessedAnswerId);
        $qb->orderBy('e.qualificationEnd', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
