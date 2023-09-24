<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\ManyToOne(targetEntity="Level", cascade={}, fetch="EAGER")
     * @ORM\JoinColumn(name="level_id", referencedColumnName="id", nullable=false)
     * @var Level
     */
    protected Level $level;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected string $name;

    /**
     * @ORM\OneToMany(targetEntity="Password", mappedBy="puzzle", cascade={"persist", "remove"}, orphanRemoval=true)
     * @var Collection<int, Password>
     */
    protected Collection $passwords;

    public function __construct(
        Level $level,
        string $name
    ) {
        $this->level = $level;
        $this->name = $name;
    }

    public function getId(): int
    {
        if (!isset($this->id)) {
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

    /**
     * @param array<Password> $passwords
     * @return void
     */
    public function setPasswords(array $passwords): void
    {
        $this->passwords = new ArrayCollection($passwords);
    }

    /**
     * @return array<Password>
     */
    public function getPasswords(): array
    {
        return $this->passwords->toArray();
    }

    /**
     * Checks whether the given answer match any of the passwords of this puzzle
     * (case-insensitive, trimmed, in ASCII only)
     *
     * @param string $answer
     * @return bool
     */
    public function isAnswerCorrect(string $answer): bool
    {
        foreach ($this->passwords as $password) {
            if ($password->match($answer)) {
                return true;
            }
        }
        return false;
    }
}
