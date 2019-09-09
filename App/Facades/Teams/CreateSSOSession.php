<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use Nette\Http\Response;

class CreateSSOSession
{
    /** @var Response */
    private $response;

    /** @var GameClockService */
    private $gameClockService;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $cookieName;

    /** @var string */
    private $cookieDomain;

    /** @var string */
    private $expiration;

    public function __construct(string $cookieName, string $cookieDomain, string $expiration, Response $response, GameClockService $gameClockService, EntityManagerInterface $entityManager)
    {
        $this->response = $response;
        $this->gameClockService = $gameClockService;
        $this->entityManager = $entityManager;
        $this->cookieName = $cookieName;
        $this->cookieDomain = $cookieDomain;
        $this->expiration = $expiration;
    }

    /**
     * @param Team $team
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateSSOSessionException
     */
    public function __invoke(Team $team): void
    {
        for ($attempts = 100; $attempts > 0; $attempts--) {
            try {
                $session = new TeamSSOSession($team, $this->gameClockService->get(), $this->gameClockService->get()->modify($this->expiration));
                $this->entityManager->persist($session);
                $this->entityManager->flush();
                $this->response->setCookie($this->cookieName, $session->getToken(), $session->getExpiration(), null, $this->cookieDomain, true, true, 'Strict');
                return;
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->entityManager->clear();
                throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateSSOSessionException;
            }
        }
    }
}
