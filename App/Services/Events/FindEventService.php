<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindEventService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given event with current id or null when no such exists
     *
     * @param int $id
     *
     * @return Event|null
     */
    public function __invoke(int $id): ?Event
    {
        return $this->entityManager->getRepository(Event::class)->find($id);
    }
}
