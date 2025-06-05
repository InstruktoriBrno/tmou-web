<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\MenuItems;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\MenuItem;

class FindMenuItemService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns menu item with given id or null when no such exists
     *
     * @param int $id
     *
     * @return MenuItem|null
     */
    public function __invoke(int $id): ?MenuItem
    {
        return $this->entityManager->getRepository(MenuItem::class)->find($id);
    }
}
