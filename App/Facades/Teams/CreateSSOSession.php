<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;
use InstruktoriBrno\TMOU\Utils\JWT;
use Nette\Http\Response;

class CreateSSOSession
{
    /** @var Response */
    private $response;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $cookieName;

    /** @var string */
    private $jwtCookieName;

    /** @var string */
    private $cookieDomain;

    /** @var string */
    private $expiration;

    /** @var JWT */
    private $jwt;

    public function __construct(string $cookieName, string $jwtCookieName, string $cookieDomain, string $expiration, Response $response, EntityManagerInterface $entityManager, JWT $jwt)
    {
        $this->response = $response;
        $this->entityManager = $entityManager;
        $this->cookieName = $cookieName;
        $this->jwtCookieName = $jwtCookieName;
        $this->cookieDomain = $cookieDomain;
        $this->expiration = $expiration;
        $this->jwt = $jwt;
    }

    /**
     * @param Team $team
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateSSOSessionException
     */
    public function __invoke(Team $team): void
    {
        $now = (new DateTimeImmutable());
        $expiration = (new DateTimeImmutable())->modify($this->expiration);
        $payload = [
            'tid' => $team->getId(),
            'tno' => $team->getNumber(),
            'tna' => $team->getName(),
            'iat' => $now->getTimestamp(),
            'nbf' => $now->getTimestamp(),
            'exp' => $expiration->getTimestamp(),
        ];
        $token = $this->jwt->encode($payload);

        try {
            $session = new TeamSSOSession(
                $team,
                $now,
                $expiration,
                $token
            );
            $this->entityManager->persist($session);
            $this->entityManager->flush();
            $this->response->setCookie($this->cookieName, $session->getToken(), $session->getExpiration(), null, $this->cookieDomain, true, true, 'Strict');
            $this->response->setCookie($this->jwtCookieName, $session->getJWT(), $session->getExpiration(), null, $this->cookieDomain, true, true, 'Strict');
            return;
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->entityManager->clear();
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateSSOSessionException;
        }
    }
}
