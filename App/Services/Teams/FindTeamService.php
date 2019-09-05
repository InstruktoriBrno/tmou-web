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
     *
     * @return Team|null
     */
    public function __invoke(int $id): ?Team
    {
        return $this->entityManager->getRepository(Team::class)->find($id);
    }
}
