<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Organizators;

use function assert;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Organizator;
use Ramsey\Uuid\Uuid;

class FindOrganizatorByKeycloakKeyService
{
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $organizatorsRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->organizatorsRepository = $entityManager->getRepository(Organizator::class);
    }

    public function __invoke(Uuid $key): ?Organizator
    {
        $object = $this->organizatorsRepository->findOneBy([
            'keycloakKey' => $key,
        ]);
        assert($object instanceof Organizator || $object === null);
        return $object;
    }
}
