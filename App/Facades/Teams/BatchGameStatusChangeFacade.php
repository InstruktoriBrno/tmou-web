<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsForMailingInEventService;
use League\Csv\Reader;
use League\Csv\Statement;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;

class BatchGameStatusChangeFacade
{
    /** @var FindTeamsForMailingInEventService */
    private $findTeamsForMailingInEventService;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        FindTeamsForMailingInEventService $findTeamsForMailingInEventService,
        EntityManagerInterface $entityManager
    ) {
        $this->findTeamsForMailingInEventService = $findTeamsForMailingInEventService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ArrayHash $values
     * @param Event $event
     * @return array{0: int, 1: int}
     * @throws \League\Csv\Exception
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\UploadCouldNotBeenProcessedException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchGameStatusException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\MalformedFormatException
     */
    public function __invoke(ArrayHash $values, Event $event): array
    {
        $batch = $values['batch'];
        if (!$batch instanceof FileUpload || !$batch->isOk()) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\UploadCouldNotBeenProcessedException;
        }
        $csv = Reader::createFromPath($batch->getTemporaryFile(), 'r');
        $csv->setHeaderOffset(0); // header is ignored
        $csv->setDelimiter(';');

        // Load given CSV
        try {
            $stmt = (new Statement());
            $records = $stmt->process($csv, ['team_id', 'new_game_status']);
        } catch (\League\Csv\Exception $exception) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\UploadCouldNotBeenProcessedException;
        }

        // Load all teams in event
        $allTeams = $this->findTeamsForMailingInEventService->findAllTeams($event);

        // Change desired states
        $changed = 0;
        foreach ($records as $record) {
            if (count($record) !== 2) {
                throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\MalformedFormatException(implode(';', $record));
            }
            if (!array_key_exists($record['team_id'], $allTeams)) {
                throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException($record['team_id']);
            }
            try {
                $gameStatus = GameStatus::fromScalar($record['new_game_status']);
            } catch (\Grifart\Enum\MissingValueDeclarationException $e) {
                throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchGameStatusException($record['new_game_status']);
            }

            $team = $allTeams[$record['team_id']];
            $team->changeTeamGameStatus($gameStatus);
            $this->entityManager->persist($team);
            $changed += 1;
            unset($allTeams[$record['team_id']]);
        }

        // Change game status of leftovers
        $other = 0;
        if ($values->other_status !== null && $values->other_status !== "") {
            $gameStatus = GameStatus::fromScalar($values->other_status);
            foreach ($allTeams as $team) {
                $team->changeTeamGameStatus($gameStatus);
                $this->entityManager->persist($team);
                $other += 1;
            }
        }
        $this->entityManager->flush();
        return [$changed, $other];
    }
}
