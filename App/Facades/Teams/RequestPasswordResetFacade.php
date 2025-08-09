<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamByEmailService;
use InstruktoriBrno\TMOU\Services\Teams\SendResetPasswordEmailService;
use Nette\Utils\Strings;

class RequestPasswordResetFacade
{
    private EntityManagerInterface $entityManager;

    private SendResetPasswordEmailService $sendResetPasswordEmailService;

    private FindTeamByEmailService $findTeamByEmailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindTeamByEmailService $findTeamByEmailService,
        SendResetPasswordEmailService $sendResetPasswordEmailService
    ) {
        $this->entityManager = $entityManager;
        $this->findTeamByEmailService = $findTeamByEmailService;
        $this->sendResetPasswordEmailService = $sendResetPasswordEmailService;
    }

    /**
     * @param string $email
     * @param Event $event
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException
     */
    public function __invoke(string $email, Event $event): void
    {
        $team = ($this->findTeamByEmailService)($event, Strings::lower($email));
        if ($team === null) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException;
        }

        $token = $team->createPasswordResetToken();

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        // Send token to team email
        ($this->sendResetPasswordEmailService)($team, $token);
    }
}
