<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="puzzle")
 */
class Puzzle
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Level")
     * @ORM\JoinColumn(name="level_id", referencedColumnName="id", nullable=false)
     * @var Level
     */
    protected Level $level;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected string $name;

    public function __construct(
        Level $level,
        string $name
    ) {
        $this->level = $level;
        $this->name = $name;
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getLevel(): Level
    {
        return $this->level;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
