<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;

/**
 * @ORM\Entity
 * @ORM\Table(name="team_sso_session")
 */
class TeamSSOSession
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
     * @ORM\JoinColumn(name="team_id", referencedColumnName="id", nullable=false)
     * @var Team
     */
    protected $team;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @var string
     */
    protected $token;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $jwt;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var boolean
     */
    protected $valid;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $expiresAt;

    public function __construct(
        Team $team,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $expiresAt,
        string $jwt
    ) {
        $this->team = $team;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->token = Random::generate(255);
        $this->jwt = $jwt;
        $this->valid = true;
    }

    public function invalidate(DateTimeImmutable $expiresAt): void
    {
        $this->valid = false;
        $this->expiresAt = $expiresAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getJWT(): ?string
    {
        return $this->jwt;
    }

    public function getExpiration(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function matchesJWTToken(string $token): bool
    {
        return $this->jwt !== null && $this->jwt === $token;
    }
}
