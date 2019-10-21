<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use InstruktoriBrno\TMOU\Utils\JWT;
use Nette\Http\Request;
use Nette\Http\Response;
use Tracy\Debugger;
use Tracy\ILogger;

class MaintainSSOSession
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

    /** @var Request */
    private $request;

    /** @var FindTeamService */
    private $findTeamService;

    /** @var JWT */
    private $jwt;

    public function __construct(
        string $cookieName,
        string $jwtCookieName,
        string $cookieDomain,
        string $expiration,
        Request $request,
        Response $response,
        EntityManagerInterface $entityManager,
        FindTeamService $findTeamService,
        JWT $jwt
    ) {
        $this->response = $response;
        $this->entityManager = $entityManager;
        $this->cookieName = $cookieName;
        $this->jwtCookieName = $jwtCookieName;
        $this->cookieDomain = $cookieDomain;
        $this->expiration = $expiration;
        $this->request = $request;
        $this->findTeamService = $findTeamService;
        $this->jwt = $jwt;
    }

    /**
     * @param int $teamId
     */
    public function __invoke(int $teamId): void
    {
        // Check if there is JWT token is not expired
        $jwt = $this->request->getCookie($this->jwtCookieName);
        if ($jwt !== null) {
            try {
                $decoded = $this->jwt->decode($jwt);
                if (isset($decoded->exp)) {
                    try {
                        $expires = new DateTimeImmutable('@' . $decoded->exp);
                        if ($expires !== null && $expires > new DateTimeImmutable()) {
                            return;
                        }
                    } catch (\Exception $e) {
                        // intentionally empty, in this case we need a new token
                    }
                }
            } catch (\Exception $e) {
                // intentionally empty, in this case we need a new token
            }
        }

        // Issues new token
        $team = ($this->findTeamService)($teamId);
        if ($team === null) {
            return;
        }
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

        for ($attempts = 2; $attempts > 0; $attempts--) {
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
                $this->entityManager->clear();
                return;
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->entityManager->clear();
                // Silent error as this should be only a background action
            }
        }
        Debugger::log('Could not maintain SSO session, token generation failed.', ILogger::EXCEPTION);
        if (isset($e)) {
            Debugger::log($e, ILogger::EXCEPTION);
        }
    }
}
