<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Team;

class FindTeamForFormService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given team data for @see \InstruktoriBrno\TMOU\Forms\TeamRegistrationFormFactory
     *
     * @param int $id
     *
     * @return array<string, mixed>
     */
    public function __invoke(int $id): array
    {
        /** @var Team $team */
        $team = $this->entityManager->getRepository(Team::class)->find($id);
        $members = [];
        foreach (range(1, 5) as $item) {
            $member = $team->getTeamMember($item);
            if ($member === null) {
                continue;
            }
            $members[$item] = [
                'fullname' => $member->getFullName(),
                'email' => $member->getEmail(),
                'age' => $member->getAge(),
                'addToNewsletter' => $member->canBeAddedToNewsletter(),
            ];
        }
        return [
            'name' => $team->getName(),
            'phrase' => $team->getPhrase(),
            'email' => $team->getEmail(),
            'phone' => $team->getPhone(),
            'members' => $members,
        ];
    }
}
