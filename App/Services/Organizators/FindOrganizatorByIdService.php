<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Organizators;

use Doctrine\Common\Persistence\ObjectRepository; // phpcs:ignore
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Organizator;

class FindOrganizatorByIdService
{
    /** @var ObjectRepository<Organizator> */
    private $organizatorsRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->organizatorsRepository = $entityManager->getRepository(Organizator::class);
    }

    public function __invoke(int $id): ?Organizator
    {
        return $this->organizatorsRepository->find($id);
    }
}
