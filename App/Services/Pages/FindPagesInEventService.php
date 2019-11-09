<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Page;

class FindPagesInEventService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns all pages from given event
     *
     * @param Event|null $event
     *
     * @return Page[]
     */
    public function __invoke(?Event $event = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(Page::class, 'p')->select('p');
        if ($event !== null) {
            $qb->where('p.event = ?1');
            $qb->setParameter(1, $event);
        } else {
            $qb->where('p.event IS NULL');
        }
        return $qb->getQuery()->getResult();
    }
}
