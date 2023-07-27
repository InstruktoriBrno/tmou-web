<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindTeamResultsByLevelsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Return all answers (only id and answered time) from given event grouped by team ID and level ID
     *
     * @param Event $event
     * @return array{int: array<int, array{answer_id: int, answered_at: DateTimeInterface}>}
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(Event $event): array
    {
        $rawData = $this->entityManager->getConnection()->executeQuery(<<<SQL
SELECT
  answer.id as answer_id,
  answer.answered_at as answered_at,
  answer.team_id as team_id,
  puzzle.id as puzzle_id,
  level_id as level_id
FROM answer
JOIN puzzle ON answer.puzzle_id = puzzle.id
JOIN level ON puzzle.level_id = level.id
WHERE level.event_id = ? AND answer.correct = TRUE
SQL, [$event->getId()], [ParameterType::INTEGER])->fetchAllAssociative();
        $output = [];
        foreach ($rawData as $row) {
            $output[$row['team_id']][$row['level_id']][$row['puzzle_id']] = [
                'answer_id' => (int) $row['answer_id'],
                'answered_at' => new DateTimeImmutable($row['answered_at']),
            ];
        }
        return $output; // @phpstan-ignore-line
    }
}
