<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Model\TeamMember;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use InstruktoriBrno\TMOU\Services\Teams\GetTeamEventNumberService;
use InstruktoriBrno\TMOU\Services\Teams\IsTeamEmailInEventUniqueService;
use InstruktoriBrno\TMOU\Services\Teams\IsTeamNameInEventUniqueService;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class ChangeTeamFacade
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

    /** @var FindTeamService */
    private $findTeamService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GetTeamEventNumberService $getTeamEventNumberService,
        FindTeamService $findTeamService,
        IsTeamEmailInEventUniqueService $isTeamEmailInEventUniqueService,
        IsTeamNameInEventUniqueService $isTeamNameInEventUniqueService,
        GameClockService $gameClockService
    ) {
        $this->entityManager = $entityManager;
        $this->gameClockService = $gameClockService;
        $this->getTeamEventNumberService = $getTeamEventNumberService;
        $this->isTeamEmailInEventUniqueService = $isTeamEmailInEventUniqueService;
        $this->isTeamNameInEventUniqueService = $isTeamNameInEventUniqueService;
        $this->findTeamService = $findTeamService;
    }

    /**
     * @param ArrayHash $values
     * @param User $user
     * @param bool $isImpersonated
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
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\OutOfRegistrationIntervalException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException
     */
    public function __invoke(ArrayHash $values, User $user, bool $isImpersonated = false): void
    {
        if (!$user->isInRole(UserRole::TEAM)) {
            throw new \InstruktoriBrno\TMOU\Exceptions\LogicException('Should not been called for non-team users.');
        }
        $team = ($this->findTeamService)($user->getId());
        if ($team === null) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException;
        }

        // Revert certain values to existing values after deadline (as their change is not allowed)
        $isAfterDeadline = $team->getEvent()->getChangeDeadlineComputed() !== null && $this->gameClockService->get() > $team->getEvent()->getChangeDeadlineComputed();
        if ($isAfterDeadline) {
            $values->name = $team->getName();
            $values->email = $team->getEmail();
            $values->phrase = $team->getPhrase();
            $values->phone = $team->getPhone();
        }

        $membersToSave = [];
        foreach (range(1, 5) as $item) {
            $member = $team->getTeamMember($item);
            /** @var ArrayHash $temp */
            $temp = $values->members;
            if ($temp->{$item}->fullname === '') {
                continue;
            }
            if ($member === null) {
                $member = new TeamMember(
                    $item,
                    $temp->{$item}->fullname,
                    $temp->{$item}->email === '' ? null : $temp->{$item}->email,
                    $temp->{$item}->age === '' ? null : (int) $temp->{$item}->age,
                    $temp->{$item}->addToNewsletter
                );
            } else {
                $member->updateDetails(
                    $temp->{$item}->fullname,
                    $temp->{$item}->email === '' ? null : $temp->{$item}->email,
                    $temp->{$item}->age === '' ? null : (int) $temp->{$item}->age,
                    $temp->{$item}->addToNewsletter
                );
            }
            $membersToSave[] = $member;
        }

        $team->updateDetails(
            $values->name,
            $values->email,
            $isImpersonated ? null : $values->oldPassword,
            $values->password === '' ? null : $values->password,
            $values->phrase,
            $values->phone,
            $this->gameClockService->get(),
            $membersToSave
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

        if ($isAfterDeadline) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\OutOfRegistrationIntervalException;
        }
    }
}
