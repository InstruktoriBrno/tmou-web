<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Model\Post;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorByIdService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class SaveNewThreadFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FindEventService */
    private $findEventService;

    /** @var FindOrganizatorByIdService */
    private $findOrganizatorByIdService;

    /** @var FindTeamService */
    private $findTeamService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindEventService $findEventService,
        FindOrganizatorByIdService $findOrganizatorByIdService,
        FindTeamService $findTeamService
    ) {
        $this->entityManager = $entityManager;
        $this->findEventService = $findEventService;
        $this->findOrganizatorByIdService = $findOrganizatorByIdService;
        $this->findTeamService = $findTeamService;
    }

    /**
     * @param ArrayHash $values
     * @param User $user
     *
     * @return Thread
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\TitleIsTooLongException
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchEventException
     *
     */
    public function __invoke(ArrayHash $values, User $user): Thread
    {
        $event = null;
        if ($values->event !== null) {
            $event = ($this->findEventService)($values->event);
            if ($event === null) {
                throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchEventException;
            }
        }
        if ($event !== null && $event->getEventEnd() !== null && $event->getEventEnd() < new DateTimeImmutable('-6 months')) {
            throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException;
        }
        $organizator = null;
        $team = null;
        if ($user->isInRole(UserRole::ORG)) {
            $organizator = ($this->findOrganizatorByIdService)($user->getId());
        }
        if ($user->isInRole(UserRole::TEAM)) {
            $team = ($this->findTeamService)($user->getId());
        }
        try {
            $thread = new Thread(
                $event,
                $values->title,
                $organizator,
                $team,
                false
            );
        } catch (\InstruktoriBrno\TMOU\Model\Exceptions\TitlelTooLongException $exception) {
            throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\TitleIsTooLongException;
        }

        $post = new Post($thread, $values->content, $organizator, $team, false);

        $this->entityManager->persist($thread);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $thread;
    }
}
