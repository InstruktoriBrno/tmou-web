<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $slug;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $heading;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     * @var Event|null
     */
    protected $event;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $cachingSafe;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $content;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $hidden;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $revealAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $lastUpdatedAt;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $isDefault;

    public function __construct(
        string $title,
        string $slug,
        string $heading,
        ?Event $event,
        string $content,
        bool $cachingSafe,
        bool $hidden,
        bool $default,
        ?DateTimeImmutable $revealAt
    ) {
        $this->title = $title;
        $this->slug = $slug;
        $this->heading = $heading;
        $this->event = $event;
        $this->content = $content;
        $this->cachingSafe = $cachingSafe;
        $this->hidden = $hidden;
        $this->isDefault = $default;
        $this->revealAt = $revealAt;
        $this->lastUpdatedAt = new DateTimeImmutable();
    }

    public function change(
        string $title,
        string $slug,
        string $heading,
        ?Event $event,
        string $content,
        bool $cachingSafe,
        bool $hidden,
        bool $default,
        ?DateTimeImmutable $revealAt
    ): void {
        $this->title = $title;
        $this->slug = $slug;
        $this->heading = $heading;
        $this->event = $event;
        $this->content = $content;
        $this->cachingSafe = $cachingSafe;
        $this->hidden = $hidden;
        $this->isDefault = $default;
        $this->revealAt = $revealAt;
        $this->lastUpdatedAt = new DateTimeImmutable();
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getHeading(): string
    {
        return $this->heading;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isCachingSafe(): bool
    {
        return $this->cachingSafe;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getRevealAt(): ?DateTimeImmutable
    {
        return $this->revealAt;
    }

    public function isRevealed(DateTimeImmutable $now): bool
    {
        return $this->hidden === false || ($this->revealAt !== null && $now > $this->revealAt);
    }

    public function getLastUpdatedAt(): ?DateTimeImmutable
    {
        return $this->lastUpdatedAt;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}
