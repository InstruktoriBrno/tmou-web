<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 * @ORM\Table(name="thread")
 */
class Thread
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Event", fetch="EAGER")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     * @var Event|null
     */
    protected $event;

    /**
     * @ORM\Column(type="text", length=191)
     * @var string
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity="Organizator")
     * @ORM\JoinColumn(name="organizator_id", referencedColumnName="id", nullable=true)
     * @var Organizator|null
     */
    protected $organizator;

    /**
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=true)
     * @var Team|null
     */
    protected $team;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $locked;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $revealAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $updatedAt;

    public function __construct(
        ?Event $event,
        string $title,
        ?Organizator $organizator,
        ?Team $team,
        bool $locked,
        ?DateTimeImmutable $revealAt = null
    ) {
        if ($organizator === null && $team === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\ThreadAuthorIsMissingException;
        }
        if ($organizator !== null && $team !== null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\TooManyThreadAuthorsException;
        }
        if (Strings::length($title) > 191) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\TitlelTooLongException;
        }

        $this->event = $event;
        $this->title = $title;
        $this->organizator = $organizator;
        $this->team = $team;
        $this->locked = $locked;
        $this->createdAt = $this->updatedAt = new DateTimeImmutable();
        $this->revealAt = $revealAt;
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function getOrganizator(): ?Organizator
    {
        return $this->organizator;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getRevealAt(): ?DateTimeImmutable
    {
        return $this->revealAt;
    }

    public function touchUpdatedAt(DateTimeImmutable $now): void
    {
        $this->updatedAt = $now;
    }

    public function isClosed(): bool
    {
        return $this->event !== null && $this->event->getEventEnd() !== null && $this->event->getEventEnd() < new DateTimeImmutable('-6 months');
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function unlock(): void
    {
        $this->locked = false;
    }

    public function changeRevealAt(?DateTimeImmutable $revealAt): void
    {
        $this->revealAt = $revealAt;
    }

    public function isHidden(DateTimeImmutable $now): bool
    {
        return $this->revealAt !== null && $now < $this->revealAt;
    }

    /**
     * @param string $title
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\TitlelTooLongException
     */
    public function changeTitle(string $title): void
    {
        if (Strings::length($title) > 191) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\TitlelTooLongException;
        }
        $this->title = $title;
    }

    public function changeEvent(?Event $event): void
    {
        $this->event = $event;
    }
}
