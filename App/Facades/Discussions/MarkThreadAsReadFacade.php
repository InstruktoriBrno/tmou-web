<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Model\ThreadAcknowledgement;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorByIdService;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadAcknowledgementByOrganizatorService;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadAcknowledgementByTeamService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use Nette\Security\User;

class MarkThreadAsReadFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FindOrganizatorByIdService */
    private $findOrganizatorByIdService;

    /** @var FindTeamService */
    private $findTeamService;

    /** @var FindThreadAcknowledgementByOrganizatorService */
    private $findThreadAcknowledgementByOrganizatorService;

    /** @var FindThreadAcknowledgementByTeamService */
    private $findThreadAcknowledgementByTeamService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindOrganizatorByIdService $findOrganizatorByIdService,
        FindTeamService $findTeamService,
        FindThreadAcknowledgementByOrganizatorService $findThreadAcknowledgementByOrganizatorService,
        FindThreadAcknowledgementByTeamService $findThreadAcknowledgementByTeamService
    ) {
        $this->entityManager = $entityManager;
        $this->findOrganizatorByIdService = $findOrganizatorByIdService;
        $this->findTeamService = $findTeamService;
        $this->findThreadAcknowledgementByOrganizatorService = $findThreadAcknowledgementByOrganizatorService;
        $this->findThreadAcknowledgementByTeamService = $findThreadAcknowledgementByTeamService;
    }

    /**
     * @param Thread $thread
     * @param User $user
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchUserException
     */
    public function __invoke(Thread $thread, User $user): void
    {
        if (!$user->isLoggedIn()) {
            return;
        }

        if ($user->isInRole(UserRole::ORG)) {
            $organizator = ($this->findOrganizatorByIdService)($user->getId());
            if ($organizator !== null) {
                $acknowledgement = ($this->findThreadAcknowledgementByOrganizatorService)($thread, $organizator);
                if ($acknowledgement === null) {
                    $acknowledgement = new ThreadAcknowledgement($thread, $organizator, null);
                } else {
                    $acknowledgement->touchAt(new DateTimeImmutable());
                }
                $this->entityManager->persist($acknowledgement);
            } else {
                throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchUserException;
            }
        }

        if ($user->isInRole(UserRole::TEAM)) {
            $team = ($this->findTeamService)($user->getId());
            if ($team !== null) {
                $acknowledgement = ($this->findThreadAcknowledgementByTeamService)($thread, $team);
                if ($acknowledgement == null) {
                    $acknowledgement = new ThreadAcknowledgement($thread, null, $team);
                } else {
                    $acknowledgement->touchAt(new DateTimeImmutable());
                }
                $this->entityManager->persist($acknowledgement);
            } else {
                throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchUserException;
            }
        }

        $this->entityManager->flush();
    }
}
