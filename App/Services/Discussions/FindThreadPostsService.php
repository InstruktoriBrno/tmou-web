<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Post;
use InstruktoriBrno\TMOU\Model\Thread;

class FindThreadPostsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all post from given thread
     *
     * @param Thread $thread
     * @return Post[]
     */
    public function __invoke(Thread $thread): array
    {
        $repository = $this->entityManager->getRepository(Post::class);
        return $repository->findBy(['thread' => $thread], ['createdAt' => 'ASC']);
    }
}
