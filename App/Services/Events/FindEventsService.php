<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindEventsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all events sorted by number in descending manner
     *
     * @return array
     */
    public function __invoke(): array
    {
        return $this->entityManager->getRepository(Event::class)->findBy([], ['number' => 'DESC']);
    }
}
