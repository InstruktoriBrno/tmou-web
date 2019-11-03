<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;

class FindThreadService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns thread with given ID
     *
     * @param int $threadId
     * @return Thread|null
     */
    public function __invoke(int $threadId): ?Thread
    {
        $repository = $this->entityManager->getRepository(Thread::class);
        return $repository->findOneBy(['id' => $threadId]);
    }
}
