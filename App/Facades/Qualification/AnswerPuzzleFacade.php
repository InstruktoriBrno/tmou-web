<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Qualification;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Answer;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Level;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Services\Qualification\FindLevelsService;
use InstruktoriBrno\TMOU\Services\Qualification\FindPuzzleService;
use InstruktoriBrno\TMOU\Services\Qualification\FindTeamAnswersCountService;
use InstruktoriBrno\TMOU\Services\Qualification\FindTeamAnswersFromLevelService;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;

class AnswerPuzzleFacade
{
    private EntityManagerInterface $entityManager;

    private FindPuzzleService $findPuzzleService;

    private FindLevelsService $findLevelsService;

    private FindTeamService $findTeamService;

    private GameClockService $gameClockService;

    private FindTeamAnswersCountService $findTeamAnswersCountService;

    private FindTeamAnswersFromLevelService $findTeamAnswersFromLevelService;


    public function __construct(
        EntityManagerInterface $entityManager,
        FindPuzzleService $findPuzzleService,
        FindLevelsService $findLevelsService,
        FindTeamService $findTeamService,
        GameClockService $gameClockService,
        FindTeamAnswersCountService $findTeamAnswersCountService,
        FindTeamAnswersFromLevelService $findTeamAnswersFromLevelService
    ) {
        $this->entityManager = $entityManager;
        $this->findPuzzleService = $findPuzzleService;
        $this->findLevelsService = $findLevelsService;
        $this->findTeamService = $findTeamService;
        $this->gameClockService = $gameClockService;
        $this->findTeamAnswersCountService = $findTeamAnswersCountService;
        $this->findTeamAnswersFromLevelService = $findTeamAnswersFromLevelService;
    }

    /**
     * Performs answering of given puzzle by given team
     *
     * @param Event $event
     * @param Team $team
     * @param int $puzzleId
     * @param string $answer
     * @return Answer
     * @throws \Exception
     */
    public function __invoke(Event $event, Team $team, int $puzzleId, string $answer): Answer
    {
        $puzzle = ($this->findPuzzleService)($puzzleId);
        if ($puzzle === null) {
            throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\NoSuchPuzzleException;
        }

        $now = $this->gameClockService->get();
        if ($now < $event->getQualificationStart() || $now > $event->getQualificationEnd()) {
            throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\OutsideQualificationException();
        }

        $levels = $this->findLevelsService->__invoke($event);
        $firstLevel = $levels[0] ?? null;

        $answerEntity = $this->entityManager->wrapInTransaction(function () use ($levels, $firstLevel, $now, $event, $answer, $puzzle, $team): Answer {
            // Prevent waiting on lock
            $this->entityManager->getConnection()->executeStatement('SET SESSION innodb_lock_wait_timeout = 0');
            // Reload the team to get latest data and lock it so no other answer evaluation can happen in parallel
            $team = $this->findTeamService->__invoke($team->getId(), LockMode::PESSIMISTIC_WRITE);
            if ($team === null) {
                throw new \InstruktoriBrno\TMOU\Exceptions\LogicException('Team not found');
            }

            // Check if the team is not can answer the puzzle
            if ($firstLevel === null || !$team->canAnswerPuzzle($puzzle, $firstLevel)) {
                throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\CannotAnswerPuzzleException;
            }

            // Refresh now as the obtaining of the lock could have taken some time
            $now = $this->gameClockService->get();

            // Check if the qualification maximum attempts has been reached
            $answersCount = ($this->findTeamAnswersCountService)($team);
            if ($answersCount >= $event->getQualificationMaxAttempts()) {
                throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\MaxQualificationAnswersCountReachedException;
            }
            // Check if answering is not blocked as a result of too many wrong answers recently
            $lastWrongAnswerAt = $team->getLastWrongAnswerAt();
            $penalizationSeconds = $event->getQualificationWrongAttemptPenalisation();
            if ($lastWrongAnswerAt !== null
                && $penalizationSeconds !== null
                && $lastWrongAnswerAt->modify('+' . $event->getQualificationWrongAttemptPenalisation() . ' seconds') > $now) {
                throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\TooManyWrongAnswersRecentlyException(
                    $lastWrongAnswerAt->modify('+' . $event->getQualificationWrongAttemptPenalisation() . ' seconds')
                );
            }

            // Ensure level is set
            if ($team->getCurrentLevel() === null) {
                $team->setCurrentLevel($firstLevel);
            }

            // Check if team have not already answered this puzzle correctly
            $levelCorrectAnswers = ($this->findTeamAnswersFromLevelService)($team, $team->getCurrentLevel(), true);
            foreach ($levelCorrectAnswers as $levelCorrectAnswer) {
                if ($levelCorrectAnswer->getPuzzle()->getId() === $puzzle->getId()) {
                    throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\AlreadyCorrectlyAnsweredException;
                }
            }

            // Evaluate the answer
            $isCorrect = $puzzle->isAnswerCorrect($answer);

            // Branch: wrong answer => update last wrong answer time,
            if (!$isCorrect) {
                $team->touchLastWrongAnswerAt($now);
                $answerEntity = new Answer($puzzle, $team, $answer, false, false, $now);
                $this->entityManager->persist($answerEntity);
                $this->entityManager->persist($team);
                return $answerEntity;
            }

            // Branch: correct answer => check if not update to the next level
            $levelAnswersCount = count($levelCorrectAnswers);
            $isLevelFulfilled = ($levelAnswersCount + 1) === $team->getCurrentLevel()->getNeededCorrectAnswers();
            if ($isLevelFulfilled) {
                $team->setCurrentLevel($this->findNextLevel($team->getCurrentLevel(), $levels));
            }
            $answerEntity = new Answer($puzzle, $team, $answer, true, $isLevelFulfilled, $now);
            $this->entityManager->persist($answerEntity);
            $this->entityManager->persist($team);
            return $answerEntity;
        });

        return $answerEntity;
    }

    /**
     * Find next level for given level in given array of all levels
     * @param Level $currentLevel
     * @param Level[] $levels
     * @return Level
     */
    private function findNextLevel(Level $currentLevel, array $levels): Level
    {
        $currentLevelNumber = $currentLevel->getLevelNumber();
        foreach ($levels as $level) {
            if ($level->getLevelNumber() === $currentLevelNumber + 1) {
                return $level;
            }
        }
        throw new \InstruktoriBrno\TMOU\Exceptions\LogicException('Next level not found');
    }
}
