<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Organizators;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Organizator;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;

class FindOrganizatorForDataGrid
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(): DoctrineDataSource
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Organizator::class, 'o')->select('o');
        return new DoctrineDataSource($qb, 'id');
    }
}
