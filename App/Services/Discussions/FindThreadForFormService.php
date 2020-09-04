<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;

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
     * @return null|array{title: string, event: int|null, revealAt: \DateTimeImmutable|null}
     */
    public function __invoke(int $threadId): ?array
    {
        /** @var Thread|null $object */
        $object = $this->entityManager->getRepository(Thread::class)->find($threadId);
        if ($object === null) {
            return null;
        }

        return [
            'title' => $object->getTitle(),
            'event' => $object->getEvent() !== null ? $object->getEvent()->getId() : null,
            'revealAt' => $object->getRevealAt(),
        ];
    }
}
