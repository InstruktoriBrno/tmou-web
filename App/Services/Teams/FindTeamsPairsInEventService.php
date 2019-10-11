<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamsPairsInEventService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ObjectRepository|EntityRepository */
    private $teamRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->teamRepository = $this->entityManager->getRepository(Team::class);
    }

    /**
     * Returns all teams of given event as id => name (id, number, state)
     *
     * @param Event $event
     * @param GameStatus[]|null $gameStates
     * @param PaymentStatus[]|null $paymentStates
     * @return array
     */
    public function __invoke(Event $event, ?array $gameStates, ?array $paymentStates): array
    {
        $args = ['event' => $event];
        if (is_array($gameStates)) {
            $args['gameStatus'] = $gameStates;
        }
        if (is_array($paymentStates)) {
            $args['paymentStatus'] = $paymentStates;
        }

        /** @var Team[] $teams */
        $teams = $this->teamRepository->findBy($args, ['name' => 'ASC']);
        $output = [];
        foreach ($teams as $team) {
            $output[$team->getId()] = sprintf('%s (ID %d, N %d, %s, %s, %s)', $team->getName(), $team->getId(), $team->getNumber(), $team->getGameStatus()->toScalar(), $team->getPaymentStatus()->toScalar(), $team->getEmail());
        }
        return $output;
    }
}
