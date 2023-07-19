<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Answer;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamAnswersService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all answers of given team
     *
     * @param Team $team
     *
     * @return Answer[]
     */
    public function __invoke(Team $team): array
    {
        return $this->entityManager->getRepository(Answer::class)->findBy(['team' => $team]);
    }
}
