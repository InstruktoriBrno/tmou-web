<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Puzzle;

class FindPuzzleService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given puzzle with current id or null when no such exists
     *
     * @param int $id
     *
     * @return Puzzle|null
     */
    public function __invoke(int $id): ?Puzzle
    {
        return $this->entityManager->getRepository(Puzzle::class)->find($id);
    }
}
