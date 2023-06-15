<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="answer")
 */
class Answer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="puzzle")
     * @ORM\JoinColumn(name="puzzle_id", referencedColumnName="id", nullable=false)
     * @var Puzzle
     */
    protected Puzzle $puzzle;

    /**
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     * @var Team
     */
    protected Team $team;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected string $code;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected bool $correct;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected bool $isLeveling;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected DateTimeImmutable $answeredAt;

    public function __construct(
        Puzzle $puzzle,
        Team $team,
        string $code,
        bool $correct,
        bool $isLeveling
    ) {
        $this->puzzle = $puzzle;
        $this->team = $team;
        $this->code = $code;
        $this->correct = $correct;
        $this->isLeveling = $isLeveling;
        $this->answeredAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        if ($this->id === null) {
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
