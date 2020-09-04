<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;
use function assert;

class FindThreadForFormService
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
     * @return array
     */
    public function __invoke(int $threadId): array
    {
        /** @var Thread $object */
        $object = $this->entityManager->getRepository(Thread::class)->find($threadId);
        assert($object !== null);

        return [
            'title' => $object->getTitle(),
            'event' => $object->getEvent() !== null ? $object->getEvent()->getId() : null,
            'revealAt' => $object->getRevealAt(),
        ];
    }
}
