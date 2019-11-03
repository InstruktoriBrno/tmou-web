<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorByIdService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use Nette\Security\User;

class MarkAllAsReadFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FindOrganizatorByIdService */
    private $findOrganizatorByIdService;

    /** @var FindTeamService */
    private $findTeamService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindOrganizatorByIdService $findOrganizatorByIdService,
        FindTeamService $findTeamService
    ) {
        $this->entityManager = $entityManager;
        $this->findOrganizatorByIdService = $findOrganizatorByIdService;
        $this->findTeamService = $findTeamService;
    }

    /**
     * @param User $user
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchUserException
     */
    public function __invoke(User $user): void
    {
        if (!$user->isLoggedIn()) {
            return;
        }

        if ($user->isInRole(UserRole::ORG)) {
            $organizator = ($this->findOrganizatorByIdService)($user->getId());
            if ($organizator !== null) {
                $organizator->touchSeenDiscussion(new DateTimeImmutable());
            } else {
                throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchUserException;
            }
            $this->entityManager->persist($organizator);
        }

        if ($user->isInRole(UserRole::TEAM)) {
            $team = ($this->findTeamService)($user->getId());
            if ($team !== null) {
                $team->touchSeenDiscussion(new DateTimeImmutable());
            } else {
                throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchUserException;
            }
            $this->entityManager->persist($team);
        }

        $this->entityManager->flush();
    }
}
