<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="post")
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Thread")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id", nullable=false)
     * @var Thread
     */
    protected $thread;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $content;

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
    protected $hidden;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $createdAt;

    public function __construct(
        Thread $thread,
        string $content,
        ?Organizator $organizator,
        ?Team $team,
        bool $hidden
    ) {
        if ($organizator === null && $team === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\PostAuthorIsMissingException;
        }
        if ($organizator !== null && $team !== null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\TooManyPostAuthorsException;
        }
        $this->thread = $thread;
        $this->content = $content;
        $this->organizator = $organizator;
        $this->team = $team;
        $this->hidden = $hidden;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
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

    public function getThread(): Thread
    {
        return $this->thread;
    }

    public function hide(): void
    {
        $this->hidden = true;
    }

    public function unhide(): void
    {
        $this->hidden = false;
    }
}
