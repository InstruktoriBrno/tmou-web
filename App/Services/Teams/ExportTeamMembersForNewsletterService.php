<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Contributte\Datagrid\Response\CsvResponse;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\TeamMember;

class ExportTeamMembersForNewsletterService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns export of team members of given event who wanted to be added into newsletter
     *
     * @param Event $event
     *
     * @return CsvResponse
     */
    public function __invoke(Event $event): CsvResponse
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(TeamMember::class, 'tm')
            ->select('tm')
            ->leftJoin('tm.team', 't')
            ->where('tm.addToNewsletter = true')
            ->andWhere('t.event = :event')
            ->setParameter('event', $event);

        $query = $qb->getQuery();
        /** @var TeamMember[] $members */
        $members = $query->getResult();
        $records = [];
        $records[] = ['fullname', 'email'];
        foreach ($members as $member) {
            if ($member->getEmail() === null) {
                continue;
            }
            $records[] = [
                $member->getFullName(),
                $member->getEmail(),
            ];
        }
        return new CsvResponse($records, 'export-newsletter.csv');
    }
}
