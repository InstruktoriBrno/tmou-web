<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Model\Post;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorByIdService;
use InstruktoriBrno\TMOU\Services\System\RememberedNicknameService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class SaveNewPostFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FindOrganizatorByIdService */
    private $findOrganizatorByIdService;

    /** @var FindTeamService */
    private $findTeamService;

    /** @var RememberedNicknameService */
    private $rememberedNicknameService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindOrganizatorByIdService $findOrganizatorByIdService,
        FindTeamService $findTeamService,
        RememberedNicknameService $rememberedNicknameService
    ) {
        $this->entityManager = $entityManager;
        $this->findOrganizatorByIdService = $findOrganizatorByIdService;
        $this->findTeamService = $findTeamService;
        $this->rememberedNicknameService = $rememberedNicknameService;
    }

    /**
     * @param Thread $thread
     * @param ArrayHash $values
     * @param User $user
     *
     * @return Post
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsLockedException
     *
     */
    public function __invoke(Thread $thread, ArrayHash $values, User $user): Post
    {
        $organizator = null;
        $team = null;
        if ($user->isInRole(UserRole::ORG)) {
            $organizator = ($this->findOrganizatorByIdService)($user->getId());
        }
        if ($user->isInRole(UserRole::TEAM)) {
            $team = ($this->findTeamService)($user->getId());
        }

        if ($thread->isClosed()) {
            throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException;
        }

        if ($thread->isLocked()) {
            throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsLockedException;
        }

        $nickname = isset($values->nickname) && $values->nickname ? $values->nickname : null;

        $thread->touchUpdatedAt(new DateTimeImmutable());
        $post = new Post($thread, $values->content, $nickname, $organizator, $team, false);

        $this->entityManager->persist($thread);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        if (isset($values->nickname)) {
            $this->rememberedNicknameService->set($values->nickname);
        }

        return $post;
    }
}
