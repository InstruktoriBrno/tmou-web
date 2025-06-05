<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

#[ORM\Entity]
#[ORM\Table(name: "event")]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\Column(type: "string", length: 255)]
    protected string $name;

    #[ORM\Column(type: "integer", unique: true)]
    protected int $number;

    #[ORM\Column(type: "float", nullable: false)]
    protected float $sorting;

    #[ORM\Column(type: "boolean")]
    protected bool $hasQualification;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $qualificationStart;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $qualificationEnd;

    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $qualifiedTeamCount;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $registrationDeadline;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $changeDeadline;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $eventStart;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $eventEnd;

    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $totalTeamCount;

    #[ORM\Column(type: "string", nullable: true)]
    protected ?string $paymentPairingCodePrefix;

    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $paymentPairingCodeSuffixLength;

    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $amount;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $paymentDeadline;

    #[ORM\Column(type: "boolean")]
    protected bool $selfreportedEntryFee;

    #[ORM\Column(type: "game_status", nullable: false)]
    protected GameStatus $afterRegistrationTeamGameStatus;

    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $qualificationMaxAttempts = null;

    #[ORM\Column(type: "boolean")]
    protected bool $qualificationShowAttemptsCount = false;

    #[ORM\Column(type: "integer", nullable: true)]
    protected ?int $qualificationWrongAttemptPenalisation = null;

    #[ORM\Column(type: "boolean")]
    protected bool $qualificationShowNextAttemptTime = false;

    public function __construct(
        string $name,
        int $number,
        bool $hasQualification,
        ?DateTimeImmutable $qualificationStart,
        ?DateTimeImmutable $qualificationEnd,
        ?int $qualifiedTeamCount,
        ?DateTimeImmutable $registrationDeadline,
        ?DateTimeImmutable $changeDeadline,
        ?DateTimeImmutable $eventStart,
        ?DateTimeImmutable $eventEnd,
        ?int $totalTeamCount,
        ?string $paymentPairingCodePrefix,
        ?int $paymentPairingCodeSuffixLength,
        ?int $amount,
        ?DateTimeImmutable $paymentDeadline,
        GameStatus $afterRegistrationTeamGameStatus,
        bool $selfreportedEntryFee = false,
        float $sorting = 0
    ) {
        static::validateDetails(
            $name,
            $number,
            $hasQualification,
            $qualificationStart,
            $qualificationEnd,
            $qualifiedTeamCount,
            $registrationDeadline,
            $changeDeadline,
            $eventStart,
            $eventEnd,
            $totalTeamCount,
            $paymentPairingCodePrefix,
            $paymentPairingCodeSuffixLength,
            $amount,
            $paymentDeadline,
            $afterRegistrationTeamGameStatus,
            $selfreportedEntryFee,
            $sorting,
        );

        $this->name = $name;
        $this->number = $number;
        $this->hasQualification = $hasQualification;
        $this->qualificationStart = $qualificationStart;
        $this->qualificationEnd = $qualificationEnd;
        $this->qualifiedTeamCount = $qualifiedTeamCount;
        $this->registrationDeadline = $registrationDeadline;
        $this->changeDeadline = $changeDeadline;
        $this->eventStart = $eventStart;
        $this->eventEnd = $eventEnd;
        $this->totalTeamCount = $totalTeamCount;
        $this->paymentPairingCodePrefix = $paymentPairingCodePrefix;
        $this->paymentPairingCodeSuffixLength = $paymentPairingCodeSuffixLength;
        $this->amount = $amount;
        $this->paymentDeadline = $paymentDeadline;
        $this->selfreportedEntryFee = $selfreportedEntryFee;
        $this->afterRegistrationTeamGameStatus = $afterRegistrationTeamGameStatus;
        $this->sorting = $sorting;
    }

    public function updateDetails(
        string $name,
        int $number,
        bool $hasQualification,
        ?DateTimeImmutable $qualificationStart,
        ?DateTimeImmutable $qualificationEnd,
        ?int $qualifiedTeamCount,
        ?DateTimeImmutable $registrationDeadline,
        ?DateTimeImmutable $changeDeadline,
        ?DateTimeImmutable $eventStart,
        ?DateTimeImmutable $eventEnd,
        ?int $totalTeamCount,
        ?string $paymentPairingCodePrefix,
        ?int $paymentPairingCodeSuffixLength,
        ?int $amount,
        ?DateTimeImmutable $paymentDeadline,
        GameStatus $afterRegistrationTeamGameStatus,
        bool $selfreportedEntryFee = false,
        float $sorting = 0
    ): void {
        static::validateDetails(
            $name,
            $number,
            $hasQualification,
            $qualificationStart,
            $qualificationEnd,
            $qualifiedTeamCount,
            $registrationDeadline,
            $changeDeadline,
            $eventStart,
            $eventEnd,
            $totalTeamCount,
            $paymentPairingCodePrefix,
            $paymentPairingCodeSuffixLength,
            $amount,
            $paymentDeadline,
            $afterRegistrationTeamGameStatus,
            $selfreportedEntryFee,
            $sorting
        );

        $this->name = $name;
        $this->number = $number;
        $this->hasQualification = $hasQualification;
        $this->qualificationStart = $qualificationStart;
        $this->qualificationEnd = $qualificationEnd;
        $this->qualifiedTeamCount = $qualifiedTeamCount;
        $this->registrationDeadline = $registrationDeadline;
        $this->changeDeadline = $changeDeadline;
        $this->eventStart = $eventStart;
        $this->eventEnd = $eventEnd;
        $this->totalTeamCount = $totalTeamCount;
        $this->paymentPairingCodePrefix = $paymentPairingCodePrefix;
        $this->paymentPairingCodeSuffixLength = $paymentPairingCodeSuffixLength;
        $this->amount = $amount;
        $this->paymentDeadline = $paymentDeadline;
        $this->afterRegistrationTeamGameStatus = $afterRegistrationTeamGameStatus;
        $this->selfreportedEntryFee = $selfreportedEntryFee;
        $this->sorting = $sorting;
    }

    public function getId(): int
    {
        if (!isset($this->id)) {
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

    public function hasQualification(): bool
    {
        return $this->hasQualification;
    }

    public function getQualificationStart(): ?DateTimeImmutable
    {
        return $this->qualificationStart;
    }

    public function getQualificationEnd(): ?DateTimeImmutable
    {
        return $this->qualificationEnd;
    }

    public function getQualifiedTeamCount(): ?int
    {
        return $this->qualifiedTeamCount;
    }

    public function getEventStart(): ?DateTimeImmutable
    {
        return $this->eventStart;
    }

    public function getEventEnd(): ?DateTimeImmutable
    {
        return $this->eventEnd;
    }

    public function getTotalTeamCount(): ?int
    {
        return $this->totalTeamCount;
    }

    public function hasQualificationInterval(): bool
    {
        return $this->qualificationStart !== null && $this->qualificationEnd !== null;
    }

    public function hasEventInterval(): bool
    {
        return $this->eventStart !== null && $this->eventEnd !== null;
    }

    public function hasNoUpperTeamLimit(): bool
    {
        return $this->totalTeamCount === null;
    }

    public function getPaymentPairingCodePrefix(): ?string
    {
        return $this->paymentPairingCodePrefix;
    }

    public function getPaymentPairingCodeSuffixLength(): ?int
    {
        return $this->paymentPairingCodeSuffixLength;
    }

    public function getRegistrationDeadline(): ?DateTimeImmutable
    {
        return $this->registrationDeadline;
    }

    public function getChangeDeadline(): ?DateTimeImmutable
    {
        return $this->changeDeadline;
    }

    public function getChangeDeadlineComputed(): ?DateTimeImmutable
    {
        if ($this->changeDeadline === null) {
            return $this->eventStart;
        }
        return $this->changeDeadline;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function getPaymentDeadline(): ?DateTimeImmutable
    {
        return $this->paymentDeadline;
    }

    public function isSelfreportedEntryFeeEnabled(): bool
    {
        return $this->selfreportedEntryFee;
    }

    public function getSorting(): float
    {
        return $this->sorting;
    }

    public function getAfterRegistrationTeamGameStatus(): GameStatus
    {
        return $this->afterRegistrationTeamGameStatus;
    }

    public static function validateDetails(
        string $name,
        int $number,
        bool $hasQualification,
        ?DateTimeImmutable $qualificationStart,
        ?DateTimeImmutable $qualificationEnd,
        ?int $qualifiedTeamCount,
        ?DateTimeImmutable $registrationDeadline,
        ?DateTimeImmutable $changeDeadline,
        ?DateTimeImmutable $eventStart,
        ?DateTimeImmutable $eventEnd,
        ?int $totalTeamCount,
        ?string $paymentPairingCodePrefix,
        ?int $paymentPairingCodeSuffixLength,
        ?int $amount,
        ?DateTimeImmutable $paymentDeadline,
        GameStatus $afterRegistrationTeamGameStatus,
        bool $selfreportedEntryFee,
        ?float $sorting
    ): void {
        if (Strings::length($name) > 255) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException();
        }
        if ($number === 0) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventNumberException("Event number must not be 0, {$number} given.");
        }
        if ($hasQualification && (($qualificationStart === null && $qualificationEnd !== null) || ($qualificationStart !== null && $qualificationEnd === null))) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MissingQualificationIntervalException();
        }
        if (!$hasQualification && ($qualificationStart !== null || $qualificationEnd !== null)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\ExcessQualificationIntervalException();
        }
        if ($hasQualification && $qualificationStart !== null && $qualificationEnd !== null && $qualificationStart >= $qualificationEnd) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventQualificationIntervalException();
        }
        if (($eventStart !== null && $eventEnd === null) || ($eventStart === null && $eventEnd !== null) || ($eventStart !== null && $eventEnd !== null && $eventStart >= $eventEnd)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventIntervalException();
        }
        if ($hasQualification && $qualifiedTeamCount === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MissingQualifiedTeamCountException();
        }
        if ($qualifiedTeamCount !== null && $totalTeamCount !== null && $qualifiedTeamCount > $totalTeamCount) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamCountException();
        }
        if ($registrationDeadline !== null && $eventStart !== null && $registrationDeadline > $eventStart) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidRegistrationDeadlineException();
        }
        if ($changeDeadline !== null && $eventStart !== null && $changeDeadline > $eventStart) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidChangeDeadlineException();
        }
        if ($changeDeadline !== null && $registrationDeadline !== null && $changeDeadline < $registrationDeadline) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\ChangeDeadlineBeforeRegistrationDeadlineException;
        }
        if ($paymentPairingCodePrefix !== null && $paymentPairingCodeSuffixLength === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentPairingCodeSuffixLengthException();
        }
        if ($paymentPairingCodePrefix === null && $paymentPairingCodeSuffixLength !== null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentPairingCodePrefixException();
        }
        if ($paymentPairingCodePrefix !== null && (!Validators::isNumericInt($paymentPairingCodePrefix) || (int) $paymentPairingCodePrefix < -1)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPaymentPairingCodePrefixException();
        }
        if ($paymentPairingCodeSuffixLength !== null && $paymentPairingCodeSuffixLength < 1) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPaymentPairingCodeSuffixLengthException();
        }
        if ($amount !== null && $amount < 0) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidAmountException();
        }
        if (($amount !== null && $amount !== 0) && $paymentDeadline === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentDeadlineException();
        }
        if (($amount === null || $amount === 0) && $paymentDeadline !== null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MissingAmountException();
        }
    }

    /**
     * Returns whether it is period after end game when team review should be reminded
     *
     * @param DateTimeImmutable $now
     * @return bool
     */
    public function isPeriodForRemindingTeamReviews(DateTimeImmutable $now): bool
    {
        return $this->eventEnd !== null && $now > $this->eventEnd && $now < $this->eventEnd->modify('+14 days');
    }

    /**
     * Returns whether it is period after the team reviews can be manipulated
     *
     * @param DateTimeImmutable $now
     * @return bool
     */
    public function isPeriodForTeamReviews(DateTimeImmutable $now): bool
    {
        return $now > $this->eventEnd;
    }

    private function validateQualificationDetails(
        ?int $qualificationMaxAttempts,
        bool $qualificationShowAttemptsCount,
        ?int $qualificationWrongAttemptPenalisation,
        bool $qualificationShowNextAttemptTime
    ): void {
        if ($qualificationMaxAttempts !== null && $qualificationMaxAttempts < 1) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidQualificationMaxAttemptsException();
        }
        if ($qualificationWrongAttemptPenalisation !== null && $qualificationWrongAttemptPenalisation < 0) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidQualificationWrongAttemptPenalisationException();
        }
    }

    /**
     * Update qualification details of this event
     *
     * @param int|null $qualificationMaxAttempts Number of wrong + correct answers per team
     * @param bool $qualificationShowAttemptsCount Whether to show number of attempts to team
     * @param int|null $qualificationWrongAttemptPenalisation Number of seconds to penalise for answer
     * @param bool $qualificationShowNextAttemptTime Whether to show next attempt time to team
     * @return void
     */
    public function updateQualificationDetails(
        ?int $qualificationMaxAttempts,
        bool $qualificationShowAttemptsCount,
        ?int $qualificationWrongAttemptPenalisation,
        bool $qualificationShowNextAttemptTime
    ): void {
        $this->validateQualificationDetails(
            $qualificationMaxAttempts,
            $qualificationShowAttemptsCount,
            $qualificationWrongAttemptPenalisation,
            $qualificationShowNextAttemptTime
        );
        $this->qualificationMaxAttempts = $qualificationMaxAttempts;
        $this->qualificationShowAttemptsCount = $qualificationShowAttemptsCount;
        $this->qualificationWrongAttemptPenalisation = $qualificationWrongAttemptPenalisation;
        $this->qualificationShowNextAttemptTime = $qualificationShowNextAttemptTime;
    }

    public function getQualificationMaxAttempts(): ?int
    {
        return $this->qualificationMaxAttempts;
    }

    public function shouldShowQualificationAttemptsCount(): bool
    {
        return $this->qualificationShowAttemptsCount;
    }

    /**
     * Returns time in seconds to penalise for wrong answer or null when no such penalty should be applied
     * @return int|null
     */
    public function getQualificationWrongAttemptPenalisation(): ?int
    {
        return $this->qualificationWrongAttemptPenalisation;
    }

    public function shouldShowQualificationNextAttemptTime(): bool
    {
        return $this->qualificationShowNextAttemptTime;
    }
}
