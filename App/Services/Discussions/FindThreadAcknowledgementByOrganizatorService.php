<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\Common\Persistence\ObjectRepository; // phpcs:ignore
use InstruktoriBrno\TMOU\Model\Organizator;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Model\ThreadAcknowledgement;
use Doctrine\ORM\EntityManagerInterface;

class FindThreadAcknowledgementByOrganizatorService
{
    /** @var ObjectRepository<ThreadAcknowledgement> */
    private $threadAcknowledgementRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->threadAcknowledgementRepository = $entityManager->getRepository(ThreadAcknowledgement::class);
    }

    public function __invoke(Thread $thread, Organizator $organizator): ?ThreadAcknowledgement
    {
        return $this->threadAcknowledgementRepository->findOneBy(['thread' => $thread, 'organizator' => $organizator]);
    }
}
