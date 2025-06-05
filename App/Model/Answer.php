<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "answer")]
class Answer
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Puzzle::class, fetch: "EAGER")]
    #[ORM\JoinColumn(name: "puzzle_id", referencedColumnName: "id", nullable: false)]
    protected Puzzle $puzzle;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: "team_id", referencedColumnName: "id")]
    protected Team $team;

    #[ORM\Column(type: "text")]
    protected string $code;

    #[ORM\Column(type: "boolean")]
    protected bool $correct;

    #[ORM\Column(type: "boolean")]
    protected bool $isLeveling;

    #[ORM\Column(type: "datetime_immutable", nullable: false)]
    protected DateTimeImmutable $answeredAt;

    public function __construct(
        Puzzle $puzzle,
        Team $team,
        string $code,
        bool $correct,
        bool $isLeveling,
        DateTimeImmutable $answeredAt
    ) {
        $this->puzzle = $puzzle;
        $this->team = $team;
        $this->code = $code;
        $this->correct = $correct;
        $this->isLeveling = $isLeveling;
        $this->answeredAt = $answeredAt;
    }

    public function getId(): int
    {
        if (!isset($this->id)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getPuzzle(): Puzzle
    {
        return $this->puzzle;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isCorrect(): bool
    {
        return $this->correct;
    }

    public function isLeveling(): bool
    {
        return $this->isLeveling;
    }

    public function getAnsweredAt(): DateTimeImmutable
    {
        return $this->answeredAt;
    }
}
