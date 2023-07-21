<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use DateTimeInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindPuzzleStatisticsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Return all puzzles from given event with statistics of fastest team, fastest time and number of teams that solved the puzzle
     *
     * @param Event $event
     * @return array<array{puzzle_name: string, solved_team_count: int, fastest_answer_time: DateTimeInterface|null, fastest_team_name: string|null}>
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(Event $event): array
    {
        return $this->entityManager->getConnection()->executeQuery( // @phpstan-ignore-line
            <<<SQL
                SELECT
                puzzle.name AS puzzle_name,
                COUNT(answer.id) AS solved_team_count,
                SEC_TO_TIME(MIN(TIMESTAMPDIFF(SECOND, event.qualification_start, answer.answered_at))) AS fastest_answer_time,
                team.name AS fastest_team_name
                FROM puzzle
                JOIN level ON level.id = puzzle.level_id
                JOIN event oN event.id = level.event_id
                LEFT JOIN answer ON answer.puzzle_id = puzzle.id AND answer.correct = TRUE
                LEFT JOIN team ON answer.team_id = team.id
                WHERE level.event_id = ?
                GROUP BY puzzle.id
                ORDER BY puzzle.id
            SQL,
            [$event->getId()],
            [ParameterType::INTEGER]
        )->fetchAllAssociative();
    }
}
