<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;

class DeleteLockThreadFacade
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Thread $thread
     */
    public function __invoke(Thread $thread): void
    {
        $this->entityManager->remove($thread);
        $this->entityManager->flush();
    }
}
