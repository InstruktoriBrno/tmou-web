<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class DeleteOldQualificationService
{
    private EntityManagerInterface $entityManager;
    private FindLevelsService $findLevelsService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindLevelsService $findLevelsService
    ) {
        $this->entityManager = $entityManager;
        $this->findLevelsService = $findLevelsService;
    }

    /**
     * Delete all levels (cascades to puzzles, passwords and answers, team reached level) and unset team last answer
     *
     * @return void
     */
    public function __invoke(Event  $event): void
    {
        $levels = ($this->findLevelsService)($event);
        foreach ($levels as $level) {
            $this->entityManager->remove($level);
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->update(Team::class, 't')
            ->set('t.lastWrongAnswerAt', 'null')
            ->where('t.event = :event')
            ->setParameter('event', $event);
        $qb->getQuery()->execute();
    }
}
