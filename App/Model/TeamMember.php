<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Validators;

/**
 * @ORM\Entity
 * @ORM\Table(name="team_member", uniqueConstraints={@ORM\UniqueConstraint(name="unique_number_in_team_idx", columns={"team_id", "number"})})
 */
class TeamMember
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Team", inversedBy="members")
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     * @var Team
     */
    protected $team;

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var integer
     */
    protected $number;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $fullName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    protected $email;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var integer|null
     */
    protected $age;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $addToNewsletter;


    public function __construct(
        int $number,
        string $fullName,
        ?string $email,
        ?int $age,
        bool $addToNewsletter
    ) {
        if ($email !== null && !Validators::isEmail($email)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEmailException("The e-mail `${email}` is invalid.");
        }

        $this->number = $number;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->age = $age;
        $this->addToNewsletter = $addToNewsletter;
    }

    public function bindToTeam(Team $team): void
    {
        if ($this->team !== null && $this->team !== $team) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\TeamMemberAlreadyBindedToTeamException;
        }
        $this->team = $team;
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function canBeAddedToNewsletter(): bool
    {
        return $this->addToNewsletter;
    }

    public function updateDetails(
        string $fullName,
        ?string $email,
        ?int $age,
        bool $addToNewsletter
    ): void {
        if (!Validators::isEmail($email)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEmailException("The e-mail `${email}` is invalid.");
        }

        $this->fullName = $fullName;
        $this->email = $email;
        $this->age = $age;
        $this->addToNewsletter = $addToNewsletter;
    }
}
