<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="password")
 */
class Password
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
     * @ORM\Column(type="text")
     * @var string
     */
    protected string $code;

    public function __construct(
        Puzzle $puzzle,
        string $code
    ) {
        $this->puzzle = $puzzle;
        $this->code = $code;
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

    public function getCode(): string
    {
        return $this->code;
    }
}
