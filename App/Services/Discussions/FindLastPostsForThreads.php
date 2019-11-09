<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use InstruktoriBrno\TMOU\Model\Post;
use InstruktoriBrno\TMOU\Model\Thread;

class FindLastPostsForThreads
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns latest post for each of given threads
     *
     * @param Thread[] $threads
     * @return Post[]
     */
    public function __invoke(array $threads): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Thread::class, 't');
        $qb->select('p');
        $qb->andWhere('t.id IN (:threads)')
            ->setParameter('threads', $threads);
        $qb->innerJoin(Post::class, 'p', Join::WITH, 'p.thread = t.id');
        $qb->leftJoin(Post::class, 'p2', Join::WITH, 'p.thread = p2.thread AND p.id < p2.id');
        $qb->andWhere('p2.id IS NULL');
        $qb->orderBy('p.id', 'DESC');
        $qb->groupBy('t.id');

        $data = $qb->getQuery()->getResult();
        $output = [];
        /** @var Post $row */
        foreach ($data as $row) {
            $output[$row->getThread()->getId()] = $row;
        }
        return $output;
    }
}
