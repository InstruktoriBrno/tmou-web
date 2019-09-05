<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\VO;

use DateTimeImmutable;
use Nette\StaticClass;

class PasswordResetTokenVO
{
    use StaticClass;

    /** @var string */
    private $token;

    /** @var DateTimeImmutable */
    private $expiration;

    public function __construct(string $token, DateTimeImmutable $expiration)
    {
        $this->token = $token;
        $this->expiration = $expiration;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiration(): DateTimeImmutable
    {
        return $this->expiration;
    }
}
