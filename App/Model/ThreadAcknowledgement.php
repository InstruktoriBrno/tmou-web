<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="thread_acknowledgement",
 *     uniqueConstraints={@ORM\UniqueConstraint(columns={"thread_id", "organizator_id", "team_id"})}
 * )
 */
class ThreadAcknowledgement
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
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $at;

    public function __construct(
        Thread $thread,
        ?Organizator $organizator,
        ?Team $team
    ) {
        if ($organizator === null && $team === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\ThreadAcknowledgementOwnerIsMissingException;
        }
        if ($organizator !== null && $team !== null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\TooManyThreadAcknowledgementOwnerException;
        }

        $this->thread = $thread;
        $this->organizator = $organizator;
        $this->team = $team;
        $this->at = new DateTimeImmutable();
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getAt(): DateTimeImmutable
    {
        return $this->at;
    }

    public function getThread(): Thread
    {
        return $this->thread;
    }

    public function touchAt(DateTimeImmutable $now): void
    {
        $this->at = $now;
    }
}
