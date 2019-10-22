<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;
use Nette\Application\IResponse;
use Nette\Application\Responses\JsonResponse;

class IsSSOValid
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $token
     * @param string|null $jwt
     * @return IResponse
     * @throws \Exception
     */
    public function __invoke(string $token, ?string $jwt): IResponse
    {
        /** @var TeamSSOSession|null $session */
        $session = $this->entityManager->getRepository(TeamSSOSession::class)->findOneBy(['token' => $token]);
        if ($session === null) {
            return new JsonResponse(['valid' => 'false']);
        }
        if ($jwt !== null && $session->matchesJWTToken($jwt) && $session->isValid() && $session->getExpiration() > new DateTimeImmutable()) {
            return new JsonResponse(['valid' => 'true']);
        }
        return new JsonResponse(['valid' => 'false']);
    }
}
