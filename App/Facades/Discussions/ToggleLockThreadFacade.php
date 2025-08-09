<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;

class ToggleLockThreadFacade
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Thread $thread
     *
     */
    public function __invoke(Thread $thread): void
    {
        if ($thread->isLocked()) {
            $thread->unlock();
        } else {
            $thread->lock();
        }

        $this->entityManager->persist($thread);
        $this->entityManager->flush();
    }
}
