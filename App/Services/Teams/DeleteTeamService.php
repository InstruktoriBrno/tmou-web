<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Team;

class DeleteTeamService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * Removes team (and all depending database stuff) with given ID
     *
     * @param int $teamId
     *
     * @throws \InstruktoriBrno\TMOU\Services\Teams\Exceptions\TeamDeleteFailedException
     */
    public function __invoke(int $teamId): void
    {
        $tableName = $this->entityManager->getClassMetadata(Team::class)->getTableName();
        try {
            $this->entityManager->getConnection()->delete($tableName, ['id' => $teamId]);
        } catch (\Doctrine\DBAL\Exception\InvalidArgumentException | \Doctrine\DBAL\DBALException $e) {
            throw new \InstruktoriBrno\TMOU\Services\Teams\Exceptions\TeamDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
