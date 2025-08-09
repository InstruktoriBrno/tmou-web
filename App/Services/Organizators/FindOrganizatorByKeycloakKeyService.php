<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Organizators;

use Doctrine\Persistence\ObjectRepository; // phpcs:ignore
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Organizator;
use Ramsey\Uuid\UuidInterface;

class FindOrganizatorByKeycloakKeyService
{
    /** @var ObjectRepository<Organizator> */
    private ObjectRepository $organizatorsRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->organizatorsRepository = $entityManager->getRepository(Organizator::class);
    }

    public function __invoke(UuidInterface $key): ?Organizator
    {
        return $this->organizatorsRepository->findOneBy([
            'keycloakKey' => $key,
        ]);
    }
}
