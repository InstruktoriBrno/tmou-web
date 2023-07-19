<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given team with current id or null when no such exists
     *
     * @param int $id
     * @param int|null $lockMode
     *
     * @return Team|null
     */
    public function __invoke(int $id, ?int $lockMode = null): ?Team
    {
        return $this->entityManager->getRepository(Team::class)->find($id, $lockMode);
    }
}
