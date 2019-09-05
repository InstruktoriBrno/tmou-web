<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Organizators;

use function assert;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Organizator;

class FindOrganizatorByIdService
{
    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $organizatorsRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->organizatorsRepository = $entityManager->getRepository(Organizator::class);
    }

    public function __invoke(int $id): ?Organizator
    {
        $object = $this->organizatorsRepository->find($id);
        assert($object instanceof Organizator || $object === null);
        return $object;
    }
}
