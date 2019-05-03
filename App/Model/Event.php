<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 * @ORM\Table(name="event")
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", unique=true)
     * @var int
     */
    protected $number;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $motto;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $hasQualification;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $qualificationStart;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $qualificationEnd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    protected $qualifiedTeamCount;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $eventStart;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $eventEnd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    protected $totalTeamCount;

    public function __construct(
        string $name,
        int $number,
        string $motto,
        bool $hasQualification,
        ?DateTimeImmutable $qualificationStart,
        ?DateTimeImmutable $qualificationEnd,
        ?int $qualifiedTeamCount,
        ?DateTimeImmutable $eventStart,
        ?DateTimeImmutable $eventEnd,
        ?int $totalTeamCount
    ) {
        static::validateDetails($name, $number, $motto, $hasQualification, $qualificationStart, $qualificationEnd, $qualifiedTeamCount, $eventStart, $eventEnd, $totalTeamCount);

        $this->name = $name;
        $this->number = $number;
        $this->motto = $motto;
        $this->hasQualification = $hasQualification;
        $this->qualificationStart = $qualificationStart;
        $this->qualificationEnd = $qualificationEnd;
        $this->qualifiedTeamCount = $qualifiedTeamCount;
        $this->eventStart = $eventStart;
        $this->eventEnd = $eventEnd;
        $this->totalTeamCount = $totalTeamCount;
    }

    public function updateDetails(
        string $name,
        int $number,
        string $motto,
        bool $hasQualification,
        ?DateTimeImmutable $qualificationStart,
        ?DateTimeImmutable $qualificationEnd,
        ?int $qualifiedTeamCount,
        ?DateTimeImmutable $eventStart,
        ?DateTimeImmutable $eventEnd,
        ?int $totalTeamCount
    ): void {
        static::validateDetails($name, $number, $motto, $hasQualification, $qualificationStart, $qualificationEnd, $qualifiedTeamCount, $eventStart, $eventEnd, $totalTeamCount);

        $this->name = $name;
        $this->number = $number;
        $this->motto = $motto;
        $this->hasQualification = $hasQualification;
        $this->qualificationStart = $qualificationStart;
        $this->qualificationEnd = $qualificationEnd;
        $this->qualifiedTeamCount = $qualifiedTeamCount;
        $this->eventStart = $eventStart;
        $this->eventEnd = $eventEnd;
        $this->totalTeamCount = $totalTeamCount;
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

    public function getMotto(): string
    {
        return $this->motto;
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

    public static function validateDetails(
        string $name,
        int $number,
        string $motto,
        bool $hasQualification,
        ?DateTimeImmutable $qualificationStart,
        ?DateTimeImmutable $qualificationEnd,
        ?int $qualifiedTeamCount,
        ?DateTimeImmutable $eventStart,
        ?DateTimeImmutable $eventEnd,
        ?int $totalTeamCount
    ): void {
        if (Strings::length($name) > 255) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException();
        }
        if (Strings::length($motto) > 255) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MottoTooLongException();
        }
        if ($number < 1) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventNumberException("Event number must be positive, {$number} given.");
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
    }
}
