<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use InstruktoriBrno\TMOU\Model\Answer;
use InstruktoriBrno\TMOU\Model\Level;
use InstruktoriBrno\TMOU\Model\Puzzle;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamAnswersFromLevelService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all answers of given team for given level
     *
     * @param Team $team
     * @param Level $level
     * @param bool $correct
     *
     * @return Answer[]
     */
    public function __invoke(Team $team, Level $level, bool $correct): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a');
        $qb->from(Answer::class, 'a')
            ->join(Puzzle::class, 'p', Join::WITH, 'a.puzzle = p');
        $qb->where('a.team = :team')
            ->setParameter('team', $team);
        $qb->andWhere('p.level = :level')
            ->setParameter('level', $level);
        $qb->andWhere('a.correct = :correct')
            ->setParameter('correct', $correct);
        $qb->orderBy('a.answeredAt', 'ASC');
        return $qb->getQuery()->getResult();
    }
}
