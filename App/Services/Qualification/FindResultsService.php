<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindResultsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Return team results for given event
     * @param Event $event
     * @return array<mixed>
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(Event $event): array
    {
        return $this->entityManager->getConnection()->executeQuery(<<<SQL
SELECT
  team.name as team_name,
  cached_qualification_results.*
FROM cached_qualification_results
JOIN team ON cached_qualification_results.team_id = team.id
WHERE cached_qualification_results.event_id = ?
ORDER BY position ASC
SQL, [$event->getId()], [ParameterType::INTEGER])->fetchAllAssociative();
    }
}
