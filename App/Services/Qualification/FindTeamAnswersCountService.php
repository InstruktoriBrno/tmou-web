<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Answer;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamAnswersCountService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns count of all answers of given team
     *
     * @param Team $team
     *
     * @return int
     */
    public function __invoke(Team $team): int
    {
        return $this->entityManager->getRepository(Answer::class)->count(['team' => $team]);
    }
}
