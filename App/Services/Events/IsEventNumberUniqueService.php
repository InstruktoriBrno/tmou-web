<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class IsEventNumberUniqueService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Checks whether given changed object can be saved with its event number
     * (due to unique constraint)
     *
     * @param Event $event
     *
     * @return bool
     */
    public function __invoke(Event $event): bool
    {
        $object = $this->entityManager->getRepository(Event::class)->findOneBy(['number' => $event->getNumber()]);

        try {
            if ($object === null || $object->getId() === $event->getId()) {
                return true;
            }
        } catch (\InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException $e) {
            return false;
        }
        return false;
    }
}
