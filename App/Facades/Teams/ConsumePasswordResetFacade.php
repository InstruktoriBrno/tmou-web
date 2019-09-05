<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamByEmailService;
use InstruktoriBrno\TMOU\Services\Teams\SendResetPasswordEmailService;
use Nette\Utils\Strings;

class ConsumePasswordResetFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SendResetPasswordEmailService */
    private $sendResetPasswordEmailService;

    /** @var FindTeamByEmailService */
    private $findTeamByEmailService;

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
     * @param string $password
     * @param string $token
     * @param Event $event
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordResetTokenException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\ExpiredPasswordResetTokenException
     */
    public function __invoke(string $email, string $password, string $token, Event $event): void
    {
        $team = ($this->findTeamByEmailService)($event, Strings::lower($email));
        if ($team === null) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException;
        }

        $team->consumePasswordResetToken($token, $password);


        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }
}
