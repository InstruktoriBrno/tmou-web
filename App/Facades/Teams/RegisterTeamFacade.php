<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Model\TeamMember;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use InstruktoriBrno\TMOU\Services\Teams\GetTeamEventNumberService;
use InstruktoriBrno\TMOU\Services\Teams\IsTeamEmailInEventUniqueService;
use InstruktoriBrno\TMOU\Services\Teams\IsTeamNameInEventUniqueService;
use InstruktoriBrno\TMOU\Services\Teams\SendRegistrationEmailService;
use Nette\Utils\ArrayHash;

class RegisterTeamFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GameClockService */
    private $gameClockService;

    /** @var GetTeamEventNumberService */
    private $getTeamEventNumberService;

    /** @var IsTeamEmailInEventUniqueService */
    private $isTeamEmailInEventUniqueService;

    /** @var IsTeamNameInEventUniqueService */
    private $isTeamNameInEventUniqueService;

    /** @var SendRegistrationEmailService */
    private $sendRegistrationEmailService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GetTeamEventNumberService $getTeamEventNumberService,
        IsTeamEmailInEventUniqueService $isTeamEmailInEventUniqueService,
        IsTeamNameInEventUniqueService $isTeamNameInEventUniqueService,
        GameClockService $gameClockService,
        SendRegistrationEmailService $sendRegistrationEmailService
    ) {
        $this->entityManager = $entityManager;
        $this->gameClockService = $gameClockService;
        $this->getTeamEventNumberService = $getTeamEventNumberService;
        $this->isTeamEmailInEventUniqueService = $isTeamEmailInEventUniqueService;
        $this->isTeamNameInEventUniqueService = $isTeamNameInEventUniqueService;
        $this->sendRegistrationEmailService = $sendRegistrationEmailService;
    }

    /**
     * @param ArrayHash $values
     * @param Event $event
     *
     * @return Team
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PhraseTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamMemberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\DuplicatedTeamMemberNumberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\EmailTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\TeamMemberAlreadyBindedToTeamException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateEmailInEventException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateNameInEventException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TakenTeamNumberException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\OutOfRegistrationIntervalException
     */
    public function __invoke(ArrayHash $values, Event $event): Team
    {
        if ($event->getRegistrationDeadline() === null || $this->gameClockService->get() > $event->getRegistrationDeadline()) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\OutOfRegistrationIntervalException;
        }
        $members = [];
        foreach (range(1, 5) as $item) {
            /** @var ArrayHash $temp */
            $temp = $values->members;
            if ($temp->{$item}->fullname !== '') {
                $members[$item] = new TeamMember(
                    $item,
                    $temp->{$item}->fullname,
                    $temp->{$item}->email === '' ? null : $temp->{$item}->email,
                    $temp->{$item}->age === '' ? null : (int) $temp->{$item}->age,
                    $temp->{$item}->addToNewsletter
                );
            }
        }

        $number = ($this->getTeamEventNumberService)($event);

        $team = new Team(
            $event,
            $number,
            $values->name,
            $values->email,
            $values->password,
            $values->phrase,
            $values->phone,
            $this->gameClockService->get(), // Ignored, should not happen, otherwise fail completely as it is runtime exception
            $members
        );

        if (!($this->isTeamNameInEventUniqueService)($team)) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateNameInEventException;
        }
        if (!($this->isTeamEmailInEventUniqueService)($team)) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateEmailInEventException;
        }

        $this->entityManager->persist($team);
        try {
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TakenTeamNumberException;
        }

        // Send e-mail to email
        ($this->sendRegistrationEmailService)($team);

        return $team;
    }
}
