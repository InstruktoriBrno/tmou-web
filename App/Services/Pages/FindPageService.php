<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Page;

class FindPageService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns page with given id or null when no such exists
     *
     * @param int $id
     *
     * @return Page|null
     */
    public function __invoke(int $id): ?Page
    {
        return $this->entityManager->getRepository(Page::class)->find($id);
    }
}
