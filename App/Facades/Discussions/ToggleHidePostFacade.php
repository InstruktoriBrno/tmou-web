<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Post;

class ToggleHidePostFacade
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Post $post
     *
     */
    public function __invoke(Post $post): void
    {
        if ($post->isHidden()) {
            $post->unhide();
        } else {
            $post->hide();
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
}
