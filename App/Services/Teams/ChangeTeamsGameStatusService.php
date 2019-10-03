<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\GameStatus;

class ChangeTeamsGameStatusService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FindTeamService */
    private $findTeamService;

    public function __construct(EntityManagerInterface $entityManager, FindTeamService $findTeamService)
    {
        $this->entityManager = $entityManager;
        $this->findTeamService = $findTeamService;
    }

    /**
     * Change game status of teams with given ID
     *
     * @param int[] $ids
     *
     * @return int
     */
    public function __invoke(array $ids, GameStatus $newGameStatus): int
    {
        $changed = 0;
        foreach ($ids as $id) {
            $team = ($this->findTeamService)($id);
            if ($team === null) {
                continue;
            }
            $team->changeTeamGameStatus($newGameStatus);
            $this->entityManager->persist($team);
            $changed += 1;
        }
        $this->entityManager->flush();
        return $changed;
    }
}
