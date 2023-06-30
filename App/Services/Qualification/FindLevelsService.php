<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Level;

class FindLevelsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all levels of particular event sorted by index in ascending manner
     *
     * @return Level[]
     */
    public function __invoke(Event  $event): array
    {
        return $this->entityManager->getRepository(Level::class)->findBy(['event' => $event], ['levelNumber' => 'ASC']);
    }
}
