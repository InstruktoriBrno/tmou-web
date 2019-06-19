<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class DeleteEventService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Removes event (and all depending database stuff) with given ID
     * @param int $eventId
     *
     * @throws \InstruktoriBrno\TMOU\Services\Events\Exceptions\EventDeleteFailedException
     */
    public function __invoke(int $eventId): void
    {
        $tableName = $this->entityManager->getClassMetadata(Event::class)->getTableName();
        try {
            $this->entityManager->getConnection()->delete($tableName, ['id' => $eventId]);
        } catch (\Doctrine\DBAL\Exception\InvalidArgumentException | \Doctrine\DBAL\DBALException $e) {
            throw new \InstruktoriBrno\TMOU\Services\Events\Exceptions\EventDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
