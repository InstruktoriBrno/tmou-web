<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\Persistence\ObjectRepository; // phpcs:ignore
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Model\ThreadAcknowledgement;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\User;

class FindThreadAcknowledgementByThreadsAndUserService
{
    /** @var ObjectRepository<ThreadAcknowledgement> */
    private ObjectRepository $threadAcknowledgementRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->threadAcknowledgementRepository = $entityManager->getRepository(ThreadAcknowledgement::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @param Thread[] $threads
     * @param User $user
     * @return array<int, ThreadAcknowledgement>
     */
    public function __invoke(array $threads, User $user): array
    {
        $conditions = ['thread' => $threads];
        if ($user->isInRole(UserRole::ORG)) {
            $conditions['organizator'] = $user->getId();
        } elseif ($user->isInRole(UserRole::TEAM)) {
            $conditions['team'] = $user->getId();
        } else {
            return [];
        }
        $data = $this->threadAcknowledgementRepository->findBy($conditions);
        $output = [];
        /** @var ThreadAcknowledgement $row */
        foreach ($data as $row) {
            $output[$row->getThread()->getId()] = clone $row; // clone is needed to prevent marking thread read before they are rendered
            $this->entityManager->detach($output[$row->getThread()->getId()]);
        }
        return $output;
    }
}
