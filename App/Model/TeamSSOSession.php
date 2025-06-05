<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;

#[ORM\Entity]
#[ORM\Table(name: "team_sso_session")]
class TeamSSOSession
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: "members")]
    #[ORM\JoinColumn(name: "team_id", referencedColumnName: "id", nullable: false)]
    protected Team $team;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    protected string $token;

    #[ORM\Column(type: "text", nullable: true)]
    protected ?string $jwt;

    #[ORM\Column(type: "boolean", nullable: false)]
    protected bool $valid;

    #[ORM\Column(type: "datetime_immutable", nullable: false)]
    protected DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable", nullable: false)]
    protected DateTimeImmutable $expiresAt;

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
