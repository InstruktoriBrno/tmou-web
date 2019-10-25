<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\MenuItems;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\MenuItem;

class DeleteMenuItemService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Removes menu item (and all depending database stuff) with given ID
     *
     * @param int $menuItemId
     *
     * @throws \InstruktoriBrno\TMOU\Services\MenuItems\Exceptions\MenuItemDeleteFailedException
     */
    public function __invoke(int $menuItemId): void
    {
        $tableName = $this->entityManager->getClassMetadata(MenuItem::class)->getTableName();
        try {
            $this->entityManager->getConnection()->delete($tableName, ['id' => $menuItemId]);
        } catch (\Doctrine\DBAL\Exception\InvalidArgumentException | \Doctrine\DBAL\DBALException $e) {
            throw new \InstruktoriBrno\TMOU\Services\MenuItems\Exceptions\MenuItemDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
