<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Services\System\GameClockService;

class FindThreadsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    private GameClockService $gameClockService;

    public function __construct(EntityManagerInterface $entityManager, GameClockService $gameClockService)
    {
        $this->entityManager = $entityManager;
        $this->gameClockService = $gameClockService;
    }

    /**
     * Returns all threads sorted by updated at descending order
     *
     * @param int $page
     * @param int $perPage
     * @param bool $isOrg
     * @return Thread[]
     */
    public function __invoke(int $page, int $perPage, bool $isOrg): array
    {
        if (!$isOrg) {
            $qb = $this->entityManager->createQueryBuilder();
            $qb->from(Thread::class, 'tr');
            $qb->select('tr');
            $qb->where($qb->expr()->orX(
                $qb->expr()->isNull('tr.revealAt'),
                $qb->expr()->lte('tr.revealAt', ':now'),
            ));
            $qb->orderBy('tr.updatedAt', 'DESC');
            $qb->setParameter('now', $this->gameClockService->get());
            return $qb->getQuery()->getResult();
        } else {
            $repository = $this->entityManager->getRepository(Thread::class);
            return $repository->findBy([], ['updatedAt' => 'DESC'], $perPage, $page * $perPage);
        }
    }
}
