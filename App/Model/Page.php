<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "page")]
class Page
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    protected ?int $id;

    #[ORM\Column(type: "text")]
    protected string $title;

    #[ORM\Column(type: "string", length: 255)]
    protected string $slug;

    #[ORM\Column(type: "text")]
    protected string $heading;

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(name: "event_id", referencedColumnName: "id", nullable: true)]
    protected ?Event $event;

    #[ORM\Column(type: "boolean", nullable: false)]
    protected bool $cachingSafe;

    #[ORM\Column(type: "text")]
    protected string $content;

    #[ORM\Column(type: "boolean")]
    protected bool $hidden;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $revealAt;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?DateTimeImmutable $lastUpdatedAt;

    #[ORM\Column(type: "boolean")]
    protected bool $isDefault;

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
        if (!isset($this->id)) {
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

    public function __clone()
    {
        $this->id = null;
    }

    public function switchToEvent(Event $event): void
    {
        $this->event = $event;
        if ($this->revealAt !== null) {
            $this->revealAt = $this->revealAt->modify('+1 year');
        }
        $this->lastUpdatedAt = new DateTimeImmutable();
    }
}
