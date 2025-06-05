<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Post;

class FindPostService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns post with given ID
     *
     * @param int $postId
     * @return Post|null
     */
    public function __invoke(int $postId): ?Post
    {
        $repository = $this->entityManager->getRepository(Post::class);
        return $repository->find($postId);
    }
}
