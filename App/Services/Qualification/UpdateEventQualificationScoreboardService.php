<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Facades\Qualification\UpdateScoreboardsFacade;
use InstruktoriBrno\TMOU\Model\Event;
use function is_numeric;

class UpdateEventQualificationScoreboardService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Drop old cached qualification results for given event and update them
     * Always run in transaction!
     * @param Event $event
     * @return int ID of latest answer from updated event
     */
    public function __invoke(Event $event): int
    {
        $qualificationCount = $event->getQualifiedTeamCount() ?? 225;

        $latestAnswerId = $this->entityManager->getConnection()->fetchOne(<<<SQL
SELECT
    MAX(answer.id)
FROM answer
JOIn team ON team.id = answer.team_id
WHERE team.event_id = ?
SQL, [$event->getId()], [ParameterType::INTEGER]);

        $this->entityManager->getConnection()->executeStatement(<<<SQL
DELETE FROM cached_qualification_results WHERE event_id = ?
SQL, [$event->getId()], [ParameterType::INTEGER]);
        $this->entityManager->getConnection()->executeStatement(<<<SQL
INSERT INTO cached_qualification_results
SELECT
  result.event_id,
  result.team_id,
  ROW_NUMBER() OVER (ORDER BY max_reached_level DESC, total_answer_count DESC, latest_answer_at ASC, latest_answer_id ASC, result.team_registered_at ASC) AS position,
  IF(total_answer_count = 0,
    FALSE,
    ROW_NUMBER() OVER (PARTITION BY total_answer_count > 0 ORDER BY max_reached_level DESC, total_answer_count DESC, latest_answer_at ASC, latest_answer_id ASC, result.team_registered_at ASC) <= ?
  ) AS qualified,
  result.max_reached_level,
  result.total_answer_count,
  max(answer.answered_at) AS latest_answer_at,
  max(answer.id) AS latest_answer_id,
  NOW()
FROM
  (
    SELECT
      team.event_id,
      team.id AS team_id,
      team.registered_at AS team_registered_at,
      MAX(level.level_number) AS max_reached_level,
      COUNT(answer.id) AS total_answer_count
    FROM team
    LEFT JOIN answer ON team.id = answer.team_id
    LEFT JOIN puzzle ON puzzle.id = answer.puzzle_id
    LEFT JOIN level ON puzzle.level_id = level.id
    WHERE team.event_id = ? AND (answer.correct = TRUE OR answer.correct IS NULL)
    GROUP BY team.event_id, team.id
  ) AS result
LEFT JOIN answer ON answer.team_id = result.team_id AND answer.correct = TRUE
LEFT JOIN puzzle ON puzzle.id = answer.puzzle_id
LEFT JOIN level ON puzzle.level_id = level.id AND level.level_number= result.max_reached_level
GROUP BY team_id
ORDER BY max_reached_level DESC, total_answer_count DESC, latest_answer_at ASC, latest_answer_id ASC, team_registered_at ASC;
SQL, [$qualificationCount, $event->getId()], [ParameterType::INTEGER, ParameterType::INTEGER]);
        // MySQL INT is signed, so this is the lowest possible value
        return $latestAnswerId && is_numeric($latestAnswerId) ? (int) $latestAnswerId : UpdateScoreboardsFacade::MYSQL_INT_MIN; // @phpstan-ignore-line
    }
}
