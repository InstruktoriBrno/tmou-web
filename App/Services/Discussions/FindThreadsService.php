<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;

class FindThreadsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all threads sorted by updated at descending order
     *
     * @param int $page
     * @param int $perPage
     * @return Thread[]
     */
    public function __invoke(int $page, int $perPage): array
    {
        $repository = $this->entityManager->getRepository(Thread::class);
        return $repository->findBy([], ['updatedAt' => 'DESC'], $perPage, $page * $perPage);
    }
}
