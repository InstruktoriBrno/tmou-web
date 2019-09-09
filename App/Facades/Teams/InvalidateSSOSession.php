<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use Nette\Http\Request;
use Nette\Http\Response;

class InvalidateSSOSession
{

    /** @var Request */
    private $request;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Response */
    private $response;

    /** @var GameClockService */
    private $gameClockService;

    /** @var string */
    private $cookieName;

    /** @var string */
    private $cookieDomain;

    public function __construct(string $cookieName, string $cookieDomain, Request $request, Response $response, EntityManagerInterface $entityManager, GameClockService $gameClockService)
    {
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->response = $response;
        $this->gameClockService = $gameClockService;
        $this->cookieName = $cookieName;
        $this->cookieDomain = $cookieDomain;
    }

    public function __invoke(): void
    {
        $token = $this->request->getCookie($this->cookieName);
        if ($token === null) {
            return;
        }
        $repository = $this->entityManager->getRepository(TeamSSOSession::class);
        /** @var TeamSSOSession|null $session */
        $session = $repository->findOneBy(['token' => $token]);
        $this->response->deleteCookie($this->cookieName, null, $this->cookieDomain, true);
        if ($session === null) {
            return;
        }
        $session->invalidate($this->gameClockService->get());
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }
}
