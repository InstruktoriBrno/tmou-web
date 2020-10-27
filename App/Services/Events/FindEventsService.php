<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use function array_column;

class FindEventsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all events sorted by COALESCE(sorting, number) in descending manner
     *
     * @return Event[]
     */
    public function __invoke(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $output = $qb->select('Event, COALESCE(Event.sorting, Event.number) as numberOrder')
            ->from(Event::class, 'Event')
            ->addOrderBy('numberOrder', 'DESC')
            ->getQuery()
            ->execute();
        return array_column($output, 0);
    }
}
