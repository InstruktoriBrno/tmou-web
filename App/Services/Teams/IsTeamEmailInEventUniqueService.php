<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Team;

class IsTeamEmailInEventUniqueService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Checks whether given changed object can be saved with its team e-mail within its event
     * (due to unique constraint)
     *
     * @param Team $team
     *
     * @return bool
     */
    public function __invoke(Team $team): bool
    {
        $object = $this->entityManager->getRepository(Team::class)->findOneBy(['email' => $team->getEmail(), 'event' => $team->getEvent()]);

        try {
            if ($object === null || $object->getId() === $team->getId()) {
                return true;
            }
        } catch (\InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException $e) {
            return false;
        }
        return false;
    }
}
