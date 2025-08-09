<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Post;
use InstruktoriBrno\TMOU\Model\Thread;

class FindCountsForThreads
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns posts count for each of given threads
     *
     * @param Thread[] $threads
     * @return array<int, int>
     */
    public function __invoke(array $threads): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Post::class, 'p');
        $qb->select('COUNT(p), p');

        $qb->andWhere('p.thread IN (:threads)')
            ->setParameter('threads', $threads);
        $qb->groupBy('p.thread');

        $data = $qb->getQuery()->getResult();
        $output = [];
        /** @var array{0: Post, 1: string} $row */
        foreach ($data as $row) {
            $output[$row[0]->getThread()->getId()] = (int) $row[1];
        }
        return $output;
    }
}
