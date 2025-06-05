<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;
use Nette\Http\Request;
use Nette\Http\Response;

class InvalidateSSOSession
{

    private Request $request;

    private EntityManagerInterface $entityManager;

    private Response $response;

    private string $cookieName;

    private string $cookieDomain;

    private string $jwtCookieName;

    public function __construct(string $cookieName, string $jwtCookieName, string $cookieDomain, Request $request, Response $response, EntityManagerInterface $entityManager)
    {
        $this->request = $request;
        $this->entityManager = $entityManager;
        $this->response = $response;
        $this->cookieName = $cookieName;
        $this->jwtCookieName = $jwtCookieName;
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
        $this->response->deleteCookie($this->jwtCookieName, null, $this->cookieDomain, true);
        if ($session === null) {
            return;
        }
        $session->invalidate(new DateTimeImmutable());
        $this->entityManager->persist($session);
        $this->entityManager->flush();
    }
}
