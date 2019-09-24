<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use Ublaboo\Responses\CSVResponse;

class ExportAllTeamsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns export of team members of given event who wanted to be added into newsletter
     *
     * @param Event $event
     *
     * @return CSVResponse
     */
    public function __invoke(Event $event): CSVResponse
    {
        /** @var Team[] $teams */
        $teams = $this->entityManager->getRepository(Team::class)->findBy(['event' => $event]);

        $records = [];
        $records[] = [
            'id',
            'number',
            'Stav týmu',
            'Stav platby',
            'Datum spárování platby',
            'Jméno týmu',
            'E-mail',
            'Telefon',
            'Tajná fráze',
            'Datum registrace',
            'Datum poslední změny',
            'Datum posledního přihlášení',
            'Jméno 1. člena',
            'Jméno 2. člena',
            'Jméno 3. člena',
            'Jméno 4. člena',
            'Jméno 5. člena',
            'E-mail 1. člena',
            'E-mail 2. člena',
            'E-mail 3. člena',
            'E-mail 4. člena',
            'E-mail 5. člena',
            'Věk 1. člena',
            'Věk 2. člena',
            'Věk 3. člena',
            'Věk 4. člena',
            'Věk 5. člena',
            'Souhlas s exportem pro newsletter 1. člena',
            'Souhlas s exportem pro newsletter 2. člena',
            'Souhlas s exportem pro newsletter 3. člena',
            'Souhlas s exportem pro newsletter 4. člena',
            'Souhlas s exportem pro newsletter 5. člena',
        ];
        foreach ($teams as $team) {
            $member1 = $team->getTeamMember(1);
            $member2 = $team->getTeamMember(2);
            $member3 = $team->getTeamMember(3);
            $member4 = $team->getTeamMember(4);
            $member5 = $team->getTeamMember(5);
            $records[] = [
                $team->getId(),
                $team->getNumber(),
                $team->getGameStatus()->toScalar(),
                $team->getPaymentStatus()->toScalar(),
                $team->getPaymentPairedAt() !== null ? $team->getPaymentPairedAt()->format('j.n.Y H:i:s') : null,
                $team->getName(),
                $team->getEmail(),
                $team->getPhone(),
                $team->getPhrase(),
                $team->getRegisteredAt()->format('j.n.Y H:i:s'),
                $team->getLastUpdatedAt()->format('j.n.Y H:i:s'),
                $team->getLastLoggedAt() !== null ? $team->getLastLoggedAt()->format('j.n.Y H:i:s') : null,
                $member1 !== null ? $member1->getFullName() : null,
                $member2 !== null ? $member2->getFullName() : null,
                $member3 !== null ? $member3->getFullName() : null,
                $member4 !== null ? $member4->getFullName() : null,
                $member5 !== null ? $member5->getFullName() : null,
                $member1 !== null ? $member1->getEmail() : null,
                $member2 !== null ? $member2->getEmail() : null,
                $member3 !== null ? $member3->getEmail() : null,
                $member4 !== null ? $member4->getEmail() : null,
                $member5 !== null ? $member5->getEmail() : null,
                $member1 !== null ? $member1->getAge() : null,
                $member2 !== null ? $member2->getAge() : null,
                $member3 !== null ? $member3->getAge() : null,
                $member4 !== null ? $member4->getAge() : null,
                $member5 !== null ? $member5->getAge() : null,
                $member1 !== null ? $member1->canBeAddedToNewsletter() : null,
                $member2 !== null ? $member2->canBeAddedToNewsletter() : null,
                $member3 !== null ? $member3->canBeAddedToNewsletter() : null,
                $member4 !== null ? $member4->canBeAddedToNewsletter() : null,
                $member5 !== null ? $member5->canBeAddedToNewsletter() : null,
            ];
        }
        return new CSVResponse($records, 'export-all.csv');
    }
}
