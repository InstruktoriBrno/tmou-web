<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Enums\UserRole;
use Nette\Security\User;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="menu_item")
 */
class MenuItem
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
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     * @var Event|null
     */
    protected $event;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $class;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $tag;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $label;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var integer
     */
    protected $weight;

    /**
     * @ORM\ManyToOne(targetEntity="Page")
     * @ORM\JoinColumn(name="target_page_id", referencedColumnName="id", nullable=true)
     * @var Page|null
     */
    protected $targetPage;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="target_event_id", referencedColumnName="id", nullable=true)
     * @var Event|null
     */
    protected $targetEvent;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $targetSlug;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected $targetUrl;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    protected $forAnonymous;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    protected $forOrganizators;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    protected $forTeams;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $revealAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $hideAt;

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
        string $content,
        ?string $title,
        ?string $class,
        ?string $tag,
        ?string $label,
        int $weight,
        ?Page $targetPage,
        ?Event $targetEvent,
        ?ReservedSLUG $targetSlug,
        ?string $targetUrl,
        bool $forAnonymous,
        bool $forOrganizators,
        bool $forTeams,
        ?DateTimeImmutable $revealAt,
        ?DateTimeImmutable $hideAt
    ) {
        static::validateDetails(
            $event,
            $content,
            $title,
            $class,
            $tag,
            $label,
            $weight,
            $targetPage,
            $targetEvent,
            $targetSlug,
            $targetUrl,
            $forAnonymous,
            $forOrganizators,
            $forTeams,
            $revealAt,
            $hideAt
        );

        $this->event = $event;
        $this->content = $content;
        $this->title = $title;
        $this->class = $class;
        $this->tag = $tag;
        $this->label = $label;
        $this->weight = $weight;
        $this->targetPage = $targetPage;
        $this->targetEvent = $targetEvent;
        $this->targetSlug = $targetSlug !== null ? (string) $targetSlug->toScalar() : null;
        $this->targetUrl = $targetUrl;
        $this->forAnonymous = $forAnonymous;
        $this->forOrganizators = $forOrganizators;
        $this->forTeams = $forTeams;
        $this->revealAt = $revealAt;
        $this->hideAt = $hideAt;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function updateDetails(
        ?Event $event,
        string $content,
        ?string $title,
        ?string $class,
        ?string $tag,
        ?string $label,
        int $weight,
        ?Page $targetPage,
        ?Event $targetEvent,
        ?ReservedSLUG $targetSlug,
        ?string $targetUrl,
        bool $forAnonymous,
        bool $forOrganizators,
        bool $forTeams,
        ?DateTimeImmutable $revealAt,
        ?DateTimeImmutable $hideAt
    ): void {
        static::validateDetails(
            $event,
            $content,
            $title,
            $class,
            $tag,
            $label,
            $weight,
            $targetPage,
            $targetEvent,
            $targetSlug,
            $targetUrl,
            $forAnonymous,
            $forOrganizators,
            $forTeams,
            $revealAt,
            $hideAt
        );

        $this->event = $event;
        $this->content = $content;
        $this->title = $title;
        $this->class = $class;
        $this->tag = $tag;
        $this->label = $label;
        $this->weight = $weight;
        $this->targetPage = $targetPage;
        $this->targetEvent = $targetEvent;
        $this->targetSlug = $targetSlug !== null ? (string) $targetSlug->toScalar() : null;
        $this->targetUrl = $targetUrl;
        $this->forAnonymous = $forAnonymous;
        $this->forOrganizators = $forOrganizators;
        $this->forTeams = $forTeams;
        $this->revealAt = $revealAt;
        $this->hideAt = $hideAt;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getTargetPage(): ?Page
    {
        return $this->targetPage;
    }

    public function getTargetEvent(): ?Event
    {
        return $this->targetEvent;
    }

    public function getTargetSlug(): ?string
    {
        return $this->targetSlug;
    }

    public function getTargetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function isForAnonymous(): bool
    {
        return $this->forAnonymous;
    }

    public function isForOrganizators(): bool
    {
        return $this->forOrganizators;
    }

    public function isForTeams(): bool
    {
        return $this->forTeams;
    }

    public function getRevealAt(): ?DateTimeImmutable
    {
        return $this->revealAt;
    }

    public function getHideAt(): ?DateTimeImmutable
    {
        return $this->hideAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Returns whether this menu item is hidden for given user in given time
     * @param User $user
     * @param DateTimeImmutable $now
     * @return bool
     */
    public function isHiddenForInTime(User $user, DateTimeImmutable $now): bool
    {
        $outcome = false;
        if ($this->hideAt !== null && $this->revealAt === null) {
            $outcome = $this->hideAt < $now;
        } elseif ($this->hideAt === null && $this->revealAt !== null) {
            $outcome = $this->revealAt > $now;
        } elseif ($this->hideAt !== null && $this->revealAt !== null) {
            if ($this->hideAt > $this->revealAt) {
                $outcome = $this->revealAt > $now || $now > $this->hideAt;
            } elseif ($this->hideAt < $this->revealAt) {
                $outcome = $this->revealAt > $now && $now > $this->hideAt;
            } else {
                $outcome = true;
            }
        }

        if ($outcome) {
            return true;
        }

        if ($this->forAnonymous && $user->isLoggedIn()) {
            return true;
        }

        if ($this->forOrganizators && $this->forTeams) {
            $outcome = !$user->isInRole(UserRole::TEAM) && !$user->isInRole(UserRole::ORG);
        } elseif ($this->forOrganizators && !$this->forTeams) {
            $outcome = !$user->isInRole(UserRole::ORG);
        } elseif (!$this->forOrganizators && $this->forTeams) {
            $outcome = !$user->isInRole(UserRole::TEAM);
        }

        return $outcome;
    }

    public static function validateDetails(
        ?Event $event,
        string $content,
        ?string $title,
        ?string $class,
        ?string $tag,
        ?string $label,
        int $weight,
        ?Page $targetPage,
        ?Event $targetEvent,
        ?ReservedSLUG $targetSlug,
        ?string $targetUrl,
        bool $forAnonymous,
        bool $forOrganizators,
        bool $forTeams,
        ?DateTimeImmutable $revealAt,
        ?DateTimeImmutable $hideAt
    ): void {
        if ($targetPage !== null &&
            ($targetEvent !== null || $targetSlug !== null || $targetUrl !== null)
        ) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MixedLinkOptionsException();
        }
        if ($targetSlug !== null &&
            ($targetPage !== null || $targetUrl !== null)
        ) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MixedLinkOptionsException();
        }
        if ($targetUrl !== null &&
            ($targetEvent !== null || $targetPage !== null || $targetSlug !== null)
        ) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\MixedLinkOptionsException();
        }
        if ($targetPage === null && $targetEvent === null && $targetSlug === null && $targetUrl === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidLinkOptionsException();
        }

        if ($targetSlug !== null && $targetEvent === null && !$targetSlug->canBeLinkedWithoutEvent()) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidLinkWithoutEventException();
        }

        if ($targetUrl !== null && !Validators::isUrl($targetUrl)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidUrlException();
        }
    }
}
