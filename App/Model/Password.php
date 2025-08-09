<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

#[ORM\Entity]
#[ORM\Table(name: "password")]
class Password
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Puzzle::class)]
    #[ORM\JoinColumn(name: "puzzle_id", referencedColumnName: "id", nullable: false)]
    protected Puzzle $puzzle;

    #[ORM\Column(type: "text")]
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
        if (!isset($this->id)) {
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

    /**
     * Returns whether the given asnwer matches the password
     * (case-insensitive, in ASCII, trimmed))
     * @param string $answer
     * @return bool
     */
    public function match(string $answer): bool
    {
        $answer = Strings::trim($answer);
        $answer = Strings::toAscii($answer);
        $answer = Strings::upper($answer);

        $code = Strings::trim($this->code);
        $code = Strings::toAscii($code);
        $code = Strings::upper($code);
        return $answer === $code;
    }
}
