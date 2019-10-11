<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindEventsWithMatchablePaymentsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all upcoming events sorted by number in descending manner
     *
     * @return Event[]
     * @throws \Exception
     */
    public function __invoke(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $q = $qb->select('e')
            ->from(Event::class, 'e')
            ->where('e.paymentDeadline >= :yesterday')
            ->setParameter('yesterday', new DateTimeImmutable('yesterday'));

        return $q->getQuery()->getResult();
    }
}
