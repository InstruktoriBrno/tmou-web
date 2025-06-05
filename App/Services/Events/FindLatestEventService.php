<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindLatestEventService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns latest event taken by event number or null when there are no events
     *
     * @return Event|null
     */
    public function __invoke(): ?Event
    {
        $objects = $this->entityManager->getRepository(Event::class)->findBy([], ['sorting' => 'DESC', 'number' => 'DESC'], 1);
        if (count($objects) === 0) {
            return null;
        }
        return $objects[0];
    }
}
