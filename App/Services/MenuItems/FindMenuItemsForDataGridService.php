<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\MenuItems;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\MenuItem;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;

class FindMenuItemsForDataGridService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns data source for grid of all men items of given event
     *
     * @param Event|null $event
     *
     * @return DoctrineDataSource
     */
    public function __invoke(?Event $event = null): DoctrineDataSource
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(MenuItem::class, 'mi')->select('mi');
        if ($event !== null) {
            $qb->where('mi.event = ?1');
            $qb->setParameter(1, $event);
        } else {
            $qb->where('mi.event IS NULL');
        }
        $qb->orderBy('mi.tag', 'ASC');
        $qb->addOrderBy('mi.weight', 'ASC');
        $qb->addOrderBy('mi.content', 'ASC');
        return new DoctrineDataSource($qb, 'id');
    }
}
