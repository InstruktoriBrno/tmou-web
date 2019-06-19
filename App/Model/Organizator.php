<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InstruktoriBrno\TMOU\Enums\OrganizatorRole;
use InstruktoriBrno\TMOU\Enums\UserRole;
use Nette\Security\Identity;
use Nette\Utils\Validators;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="organizator")
 */
class Organizator
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $givenName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $familyName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="uuid_binary", unique=true)
     * @var Uuid
     */
    protected $keycloakKey;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @var DateTimeImmutable|null
     */
    protected $lastLogin;

    /**
     * @ORM\Column(type="organizator_role", nullable=true)
     * @var OrganizatorRole|null
     */
    protected $role;

    public function __construct(
        string $givenName,
        string $familyName,
        string $username,
        string $email,
        Uuid $keycloakKey,
        ?OrganizatorRole $role
    ) {
        if (!Validators::isEmail($email)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEmailException("The e-mail `${email}` is invalid.");
        }

        $this->givenName = $givenName;
        $this->familyName = $familyName;
        $this->username = $username;
        $this->email = $email;
        $this->keycloakKey = $keycloakKey;
        $this->role = $role;
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getKeycloakKey(): Uuid
    {
        return $this->keycloakKey;
    }

    public function getLastLogin(): ?DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function getRole(): ?OrganizatorRole
    {
        return $this->role;
    }

    public function touchLastLogin(): void
    {
        $this->lastLogin = new DateTimeImmutable();
    }

    public function updateDetails(
        string $givenName,
        string $familyName,
        string $username,
        string $email,
        ?OrganizatorRole $role
    ): void {
        if (!Validators::isEmail($email)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEmailException("The e-mail `${email}` is invalid.");
        }

        $this->givenName = $givenName;
        $this->familyName = $familyName;
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
    }

    public function toIdentity(): Identity
    {
        return new Identity(
            $this->getId(),
            [UserRole::ORG],
            [
                'givenName' => $this->givenName,
                'familyName' => $this->familyName,
                'username' => $this->username,
                'email' => $this->email,
            ]
        );
    }
}
