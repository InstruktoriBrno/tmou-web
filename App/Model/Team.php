<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\VO\PasswordResetTokenVO;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\Utils\Random;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="team",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unique_number_in_event_idx", columns={"event_id", "number"}),
 *          @ORM\UniqueConstraint(name="unique_name_in_event_idx", columns={"event_id", "name"}),
 *          @ORM\UniqueConstraint(name="unique_email_in_event_idx", columns={"event_id", "email"})
 *     }
 * )
 */
class Team
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * @var Event
     */
    protected $event;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $number;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $passwordHash;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    protected $passwordResetToken;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $passwordResetTokenExpiresAt;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $phrase;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $phone;

    /**
     * @ORM\Column(type="game_status", nullable=false)
     * @var GameStatus
     */
    protected $gameStatus;

    /**
     * @ORM\Column(type="payment_status", nullable=false)
     * @var PaymentStatus
     */
    protected $paymentStatus;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $paymentPairedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $registeredAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $lastUpdatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $lastLoggedAt;


    /**
     * @ORM\OneToMany(targetEntity="TeamMember", mappedBy="team", cascade={"persist", "remove"}, orphanRemoval=true)
     * @var TeamMember[]|Collection
     */
    protected $members;

    /**
     * @ORM\ManyToOne(targetEntity="TeamReview", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="team_review_id", referencedColumnName="id", nullable=true)
     * @var TeamReview|null
     */
    protected $review;

    /**
     * Team constructor.
     * @param Event $event
     * @param int $number
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $phrase
     * @param string $phone
     * @param DateTimeImmutable $registeredAt
     * @param TeamMember[] $members
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PhraseTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamMemberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\DuplicatedTeamMemberNumberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\EmailTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\TeamMemberAlreadyBindedToTeamException
     */
    public function __construct(
        Event $event,
        int $number,
        string $name,
        string $email,
        string $password,
        string $phrase,
        string $phone,
        DateTimeImmutable $registeredAt,
        array $members
    ) {
        self::validateDetails($name, $phrase, $email, null, $members, $password);

        $this->event = $event;
        $this->number = $number;
        $this->name = $name;
        $this->email = Strings::lower($email);
        $this->passwordHash = Passwords::hash($password);
        $this->phrase = $phrase;
        $this->phone = $phone;
        $this->registeredAt = $this->lastUpdatedAt = $registeredAt;

        $this->gameStatus = GameStatus::REGISTERED();
        $this->paymentStatus = PaymentStatus::NOT_PAID();

        foreach ($members as $member) {
            $member->bindToTeam($this);
        }
        $this->members = new ArrayCollection($members);
    }

    /**
     * @param string $name
     * @param string $email
     * @param string $password|null
     * @param string $newPassword|null
     * @param string $phrase
     * @param string $phone
     * @param DateTimeImmutable $updatedAt
     * @param TeamMember[] $members
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PhraseTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamMemberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\DuplicatedTeamMemberNumberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\EmailTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\TeamMemberAlreadyBindedToTeamException
     */
    public function updateDetails(
        string $name,
        string $email,
        string $password,
        ?string $newPassword,
        string $phrase,
        string $phone,
        DateTimeImmutable $updatedAt,
        array $members
    ): void {
        $this->validateDetails($name, $phrase, $email, $password, $members, $newPassword);

        $this->name = $name;
        $this->email = Strings::lower($email);
        if ($newPassword !== null) {
            $this->passwordHash = Passwords::hash($newPassword);
        }
        $this->phrase = $phrase;
        $this->phone = $phone;
        $this->lastUpdatedAt = $updatedAt;

        // Add new
        $memberNumbers = [];
        foreach ($members as $member) {
            if (in_array($member->getNumber(), $memberNumbers, true)) {
                throw new \InstruktoriBrno\TMOU\Model\Exceptions\DuplicatedTeamMemberNumberException;
            }
            $memberNumbers[] = $member->getNumber();
            if (!$this->members->contains($member)) {
                $this->members->add($member);
            }
        }
        // Delete old
        foreach ($this->members as $member) {
            foreach ($members as $member2) {
                if ($member === $member2) {
                    continue 2;
                }
            }
            $this->members->removeElement($member);
        }

        // Ensure proper team member binding
        foreach ($members as $member) {
            $member->bindToTeam($this);
        }
    }

    public function changeTeamGameStatus(GameStatus $gameStatus): void
    {
        $this->gameStatus = $gameStatus;
    }

    public function getPaymentPairingCode(): ?string
    {
        if ($this->event->getPaymentPairingCodePrefix() === null || $this->event->getPaymentPairingCodeSuffixLength() === null) {
            return null;
        }
        return sprintf('%s%s', $this->event->getPaymentPairingCodePrefix(), str_pad((string) $this->number, $this->event->getPaymentPairingCodeSuffixLength(), '0', STR_PAD_LEFT));
    }

    public function hasPaid(): bool
    {
        return $this->paymentStatus !== null && $this->paymentStatus->equals(PaymentStatus::PAID());
    }

    public function markAsPaid(DateTimeImmutable $paymentPairedAt): void
    {
        $this->paymentStatus = PaymentStatus::PAID();
        $this->paymentPairedAt = $paymentPairedAt;
    }

    public function touchLoggedAt(DateTimeImmutable $loggedAt): void
    {
        $this->lastLoggedAt = $loggedAt;
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getPhrase(): string
    {
        return $this->phrase;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getPaymentStatus(): PaymentStatus
    {
        return $this->paymentStatus;
    }

    public function getGameStatus(): GameStatus
    {
        return $this->gameStatus;
    }

    public function getPaymentPairedAt(): ?DateTimeImmutable
    {
        return $this->paymentPairedAt;
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function getLastUpdatedAt(): DateTimeImmutable
    {
        return $this->lastUpdatedAt;
    }

    public function getLastLoggedAt(): ?DateTimeImmutable
    {
        return $this->lastLoggedAt;
    }

    public function checkPassword(string $passwordRaw): bool
    {
        return Passwords::verify($passwordRaw, $this->passwordHash);
    }

    public function createPasswordResetToken(): PasswordResetTokenVO
    {
        $this->passwordResetToken = Random::generate(30);
        $this->passwordResetTokenExpiresAt = (new DateTimeImmutable())->modify('+1 day');
        return new PasswordResetTokenVO($this->passwordResetToken, $this->passwordResetTokenExpiresAt);
    }

    /**
     * @param string $token
     * @param string $password
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordResetTokenException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\ExpiredPasswordResetTokenException
     */
    public function consumePasswordResetToken(string $token, string $password): void
    {
        if (Strings::length($password) < 8) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException;
        }
        if ($this->passwordResetToken === null || $token !== $this->passwordResetToken) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordResetTokenException;
        }
        if ($this->passwordResetTokenExpiresAt < new DateTimeImmutable()) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\ExpiredPasswordResetTokenException;
        }
        $this->passwordResetTokenExpiresAt = null;
        $this->passwordResetToken = null;
        $this->passwordHash = Passwords::hash($password);
    }

    private function validateDetails(
        string $name,
        string $phrase,
        string $email,
        ?string $password,
        array $members,
        ?string $newPassword
    ): void {
        if (Strings::length($name) > 191) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException;
        }
        if (Strings::length($phrase) > 255) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\PhraseTooLongException;
        }
        if (Strings::length($email) > 255) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\EmailTooLongException;
        }
        if ($newPassword !== null && Strings::length($newPassword) < 8) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException;
        }
        if ($this->passwordHash !== null && $password !== null && !$this->checkPassword($password)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordException;
        }
        $numbers = [];
        foreach ($members as $member) {
            if (!$member instanceof TeamMember) {
                throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamMemberException;
            }
            if (in_array($member->getNumber(), $numbers, true)) {
                throw new \InstruktoriBrno\TMOU\Model\Exceptions\DuplicatedTeamMemberNumberException;
            }
            $numbers[] = $member->getNumber();
        }
    }

    public function getTeamMember(int $number): ?TeamMember
    {
        foreach ($this->members as $member) {
            if ($member->getNumber() === $number) {
                return $member;
            }
        }
        return null;
    }

    /**
     * @return TeamMember[]
     */
    public function getMembers(): array
    {
        return $this->members->toArray();
    }

    public function getMembersCount(): int
    {
        return $this->members->count();
    }

    public function getMembersAverageAge(): ?float
    {
        $values = [];
        foreach ($this->members as $member) {
            if ($member->getAge() !== null) {
                $values[] = $member->getAge();
            }
        }
        if (count($values) === 0) {
            return null;
        }
        return (float) (array_sum($values) / count($values));
    }

    public function addReview(TeamReview $teamReview): void
    {
        if ($this->review !== null && $this->review !== $teamReview) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MultipleTeamReviewsException;
        }
        $this->review = $teamReview;
    }

    public function toIdentity(): Identity
    {
        return new Identity(
            $this->getId(),
            [UserRole::TEAM],
            [
                'eventNumber' => $this->getEvent()->getNumber(),
                'name' => $this->name,
                'email' => $this->email,
            ]
        );
    }
}
