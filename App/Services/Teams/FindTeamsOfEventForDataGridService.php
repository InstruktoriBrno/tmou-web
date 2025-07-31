<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use Contributte\DataGrid\DataSource\DoctrineDataSource;

class FindTeamsOfEventForDataGridService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns data source for grid of all teams from given event
     * @param Event $event
     *
     * @return DoctrineDataSource
     */
    public function __invoke(Event $event): DoctrineDataSource
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Team::class, 't')->select('t')->where('t.event = ?1');
        $qb->setParameter(1, $event->getId());
        return new DoctrineDataSource($qb, 'id');
    }
}
