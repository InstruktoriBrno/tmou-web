<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Puzzle;
use InstruktoriBrno\TMOU\Model\Team;

class FindPuzzlesOfEventPairsService
{
    private EntityManagerInterface $entityManager;

    private FindLevelsService $findLevelsService;

    private FindTeamAnswersService $findTeamAnswersService;

    public function __construct(EntityManagerInterface $entityManager, FindLevelsService $findLevelsService, FindTeamAnswersService $findTeamAnswersService)
    {
        $this->entityManager = $entityManager;
        $this->findLevelsService = $findLevelsService;
        $this->findTeamAnswersService = $findTeamAnswersService;
    }

    /**
     * Returns all puzzles sorted by puzzle order that are accessible to the given team
     *
     * @param Event $event
     * @param Team $team
     * @return array<int, string>
     */
    public function __invoke(Event  $event, Team $team): array
    {
        $firstLevel = $this->findLevelsService->__invoke($event)[0] ?? null;
        $qb = $this->entityManager->createQueryBuilder()
            ->from(Puzzle::class, 'puzzle')
            ->join('puzzle.level', 'level')
            ->select('puzzle')
            ->where('level.event = :event')
            ->setParameter('event', $event)
            ->orderBy('level.levelNumber', 'ASC');
        $data = $qb->getQuery()->getResult();

        $result = [];
        /** @var Puzzle $puzzle */
        foreach ($data as $puzzle) {
            if ($firstLevel === null || !$team->canAnswerPuzzle($puzzle, $firstLevel)) {
                continue;
            }
            $result[$puzzle->getId()] = $puzzle->getName();
        }

        $alreadyAnswered = ($this->findTeamAnswersService)($team);
        foreach ($alreadyAnswered as $answer) {
            if ($answer->isCorrect()) {
                unset($result[$answer->getPuzzle()->getId()]);
            }
        }

        return $result;
    }
}
