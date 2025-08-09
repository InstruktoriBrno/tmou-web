<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "post")]
class Post
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Thread::class)]
    #[ORM\JoinColumn(name: "thread_id", referencedColumnName: "id", nullable: false)]
    protected Thread $thread;

    #[ORM\Column(type: "text")]
    protected string $content;

    #[ORM\Column(type: "text", nullable: true)]
    protected ?string $nickname;

    #[ORM\ManyToOne(targetEntity: Organizator::class)]
    #[ORM\JoinColumn(name: "organizator_id", referencedColumnName: "id", nullable: true)]
    protected ?Organizator $organizator;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: "team_id", referencedColumnName: "id", nullable: true)]
    protected ?Team $team;

    #[ORM\Column(type: "boolean")]
    protected bool $hidden;

    #[ORM\Column(type: "datetime_immutable", nullable: false)]
    protected DateTimeImmutable $createdAt;

    public function __construct(
        Thread $thread,
        string $content,
        ?string $nickname,
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
        $this->nickname = $nickname;
        $this->organizator = $organizator;
        $this->team = $team;
        $this->hidden = $hidden;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        if (!isset($this->id)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
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
