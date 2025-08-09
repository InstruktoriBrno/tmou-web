<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;

class ChangeTeamsCanChangeGameTimeService
{
    private EntityManagerInterface $entityManager;

    private FindTeamService $findTeamService;

    public function __construct(EntityManagerInterface $entityManager, FindTeamService $findTeamService)
    {
        $this->entityManager = $entityManager;
        $this->findTeamService = $findTeamService;
    }

    /**
     * Change whether the team can change game time
     *
     * @param int[] $ids
     * @param bool $newStatus
     *
     * @return int
     */
    public function __invoke(array $ids, bool $newStatus): int
    {
        $changed = 0;
        foreach ($ids as $id) {
            $team = ($this->findTeamService)($id);
            if ($team === null) {
                continue;
            }
            $team->setCanChangeGameTime($newStatus);
            $this->entityManager->persist($team);
            $changed += 1;
        }
        $this->entityManager->flush();
        return $changed;
    }
}
