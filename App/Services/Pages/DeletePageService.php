<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Page;

class DeletePageService
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Removes page (and all depending database stuff) with given ID
     *
     * @param int $pageId
     *
     * @throws \InstruktoriBrno\TMOU\Services\Pages\Exceptions\PageDeleteFailedException
     */
    public function __invoke(int $pageId): void
    {
        $tableName = $this->entityManager->getClassMetadata(Page::class)->getTableName();
        try {
            $this->entityManager->getConnection()->delete($tableName, ['id' => $pageId]);
        } catch (\Doctrine\DBAL\Exception\InvalidArgumentException | \Doctrine\DBAL\Exception $e) {
            throw new \InstruktoriBrno\TMOU\Services\Pages\Exceptions\PageDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
