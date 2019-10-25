<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Model\TeamReview;

class FindEventTeamReviewsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all team reviews from given event sorted by addition date in asceding manner
     *
     * @return array
     */
    public function __invoke(Event $event): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t');
        $qb->from(TeamReview::class, 'tr')
            ->join(Team::class, 't');
        $qb->where('t.event = :event')
            ->setParameter('event', $event);
        $qb->orderBy('tr.id', 'ASC');
        return $qb->getQuery()->getResult();
    }
}
