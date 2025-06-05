<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ChangeTeamsPaymentStatusService
{
    private EntityManagerInterface $entityManager;
    private FindTeamService $findTeamService;

    public function __construct(EntityManagerInterface $entityManager, FindTeamService $findTeamService)
    {
        $this->entityManager = $entityManager;
        $this->findTeamService = $findTeamService;
    }

    /**
     * Change payment status of teams with given ID
     *
     * @param int[] $ids
     * @param bool $newPaymentStatus
     *
     * @return int
     */
    public function __invoke(array $ids, bool $newPaymentStatus): int
    {
        $changed = 0;
        foreach ($ids as $id) {
            $team = ($this->findTeamService)($id);
            if ($team === null) {
                continue;
            }
            if ($newPaymentStatus) {
                $team->markAsPaid(new DateTimeImmutable());
            } else {
                $team->unmarkAsPaid();
            }
            $this->entityManager->persist($team);
            $changed += 1;
        }
        $this->entityManager->flush();
        return $changed;
    }
}
