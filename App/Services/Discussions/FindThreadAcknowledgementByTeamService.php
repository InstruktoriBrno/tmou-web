<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\Persistence\ObjectRepository; // phpcs:ignore
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Model\ThreadAcknowledgement;
use Doctrine\ORM\EntityManagerInterface;

class FindThreadAcknowledgementByTeamService
{
    /** @var ObjectRepository<ThreadAcknowledgement> */
    private ObjectRepository $threadAcknowledgementRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->threadAcknowledgementRepository = $entityManager->getRepository(ThreadAcknowledgement::class);
    }

    public function __invoke(Thread $thread, Team $team): ?ThreadAcknowledgement
    {
        return $this->threadAcknowledgementRepository->findOneBy(['thread' => $thread, 'team' => $team]);
    }
}
