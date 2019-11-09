<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use InstruktoriBrno\TMOU\Model\Organizator;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Model\ThreadAcknowledgement;
use function assert;
use Doctrine\ORM\EntityManagerInterface;

class FindThreadAcknowledgementByOrganizatorService
{
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $threadAcknowledgementRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->threadAcknowledgementRepository = $entityManager->getRepository(ThreadAcknowledgement::class);
    }

    public function __invoke(Thread $thread, Organizator $organizator): ?ThreadAcknowledgement
    {
        $object = $this->threadAcknowledgementRepository->findOneBy(['thread' => $thread, 'organizator' => $organizator]);
        assert($object instanceof ThreadAcknowledgement || $object === null);
        return $object;
    }
}
