<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Page;
use Contributte\Datagrid\DataSource\DoctrineDataSource;

class FindPagesForDataGridService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns data source for grid of all pages
     *
     * @param Event|null $event
     *
     * @return DoctrineDataSource
     */
    public function __invoke(?Event $event = null): DoctrineDataSource
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Page::class, 'p')->select('p');
        if ($event !== null) {
            $qb->where('p.event = ?1');
            $qb->setParameter(1, $event);
        } else {
            $qb->where('p.event IS NULL');
        }
        return new DoctrineDataSource($qb, 'id');
    }
}
