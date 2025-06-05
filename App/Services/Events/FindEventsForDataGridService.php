<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;

class FindEventsForDataGridService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns data source for grid of all events
     * @return DoctrineDataSource
     */
    public function __invoke(): DoctrineDataSource
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Event::class, 'e')->select('e');
        return new DoctrineDataSource($qb, 'id');
    }
}
