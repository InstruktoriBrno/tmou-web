<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindEventByNumberService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given event with given number or null when no such exists
     *
     * @param int $number
     *
     * @return Event|null
     */
    public function __invoke(int $number): ?Event
    {
        return $this->entityManager->getRepository(Event::class)->findOneBy(['number' => $number]);
    }
}
