<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Qualification\FindResultsService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;

class QualifyTeamsByQualificationFacade
{
    private EntityManagerInterface $entityManager;

    private FindTeamService $findTeamService;

    private FindResultsService $findResultsService;


    public function __construct(
        EntityManagerInterface $entityManager,
        FindTeamService $findTeamService,
        FindResultsService $findResultsService
    ) {

        $this->entityManager = $entityManager;
        $this->findTeamService = $findTeamService;
        $this->findResultsService = $findResultsService;
    }

    /**
     * Promotes qualification teams to qualified/not qualified status
     * Note: does not change those that are in different status than registered
     *
     * @param Event $event
     * @return array{0: int, 1: int, 2: int}
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(Event $event): array
    {
        $results = ($this->findResultsService)($event);
        $qualified = 0;
        $notQualified = 0;
        $intact = 0;
        foreach ($results as $team) {
            $teamEntity = ($this->findTeamService)((int) $team['team_id']);
            if ($teamEntity === null) {
                throw new \InstruktoriBrno\TMOU\Exceptions\LogicException('Team not found, but exists in results.');
            }
            if ($teamEntity->getGameStatus()->equals(GameStatus::REGISTERED())) {
                if ($team['qualified'] === true || $team['qualified'] === 1 || $team['qualified'] === '1') {
                    $teamEntity->changeTeamGameStatus(GameStatus::QUALIFIED());
                    $qualified++;
                } else {
                    $teamEntity->changeTeamGameStatus(GameStatus::NOT_QUALIFIED());
                    $notQualified++;
                }
                $this->entityManager->persist($teamEntity);
            } else {
                $intact++;
            }
        }
        $this->entityManager->flush();
        return [$qualified, $notQualified, $intact];
    }
}
