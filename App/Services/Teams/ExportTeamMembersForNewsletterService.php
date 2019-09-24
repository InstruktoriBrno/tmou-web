<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\TeamMember;
use Ublaboo\Responses\CSVResponse;

class ExportTeamMembersForNewsletterService
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
        /** @var TeamMember[] $members */
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(TeamMember::class, 'tm')
            ->select('tm')
            ->leftJoin('tm.team', 't')
            ->where('tm.addToNewsletter = true')
            ->andWhere('t.event = :event')
            ->setParameter('event', $event);

        $query = $qb->getQuery();
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
        return new CSVResponse($records, 'export-newsletter.csv');
    }
}
