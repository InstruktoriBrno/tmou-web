<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use Nette\Http\Request;
use Nette\Http\Response;
use Tracy\Debugger;
use Tracy\ILogger;

class MaintainSSOSession
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

    /** @var Request */
    private $request;

    /** @var FindTeamService */
    private $findTeamService;

    public function __construct(
        string $cookieName,
        string $cookieDomain,
        string $expiration,
        Request $request,
        Response $response,
        GameClockService $gameClockService,
        EntityManagerInterface $entityManager,
        FindTeamService $findTeamService
    ) {
        $this->response = $response;
        $this->gameClockService = $gameClockService;
        $this->entityManager = $entityManager;
        $this->cookieName = $cookieName;
        $this->cookieDomain = $cookieDomain;
        $this->expiration = $expiration;
        $this->request = $request;
        $this->findTeamService = $findTeamService;
    }

    /**
     * @param int $teamId
     */
    public function __invoke(int $teamId): void
    {
        if ($this->request->getCookie($this->cookieName) !== null) {
            return;
        }
        $team = ($this->findTeamService)($teamId);
        if ($team === null) {
            return;
        }
        for ($attempts = 2; $attempts > 0; $attempts--) {
            try {
                $session = new TeamSSOSession($team, $this->gameClockService->get(), $this->gameClockService->get()->modify($this->expiration));
                $this->entityManager->persist($session);
                $this->entityManager->flush();
                $this->response->setCookie($this->cookieName, $session->getToken(), $session->getExpiration(), null, $this->cookieDomain, true, true, 'Strict');
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
