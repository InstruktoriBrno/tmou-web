<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\TeamSSOSession;

class CleanSSOSessions
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->delete(TeamSSOSession::class, 's')
            ->where('s.valid = :valid OR s.expiresAt < :now')
            ->setParameter('valid', false)
            ->setParameter('now', new DateTimeImmutable());
        $qb->getQuery()->execute();
    }
}
