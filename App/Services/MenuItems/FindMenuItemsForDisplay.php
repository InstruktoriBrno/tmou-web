<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\MenuItems;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\MenuItem;

class FindMenuItemsForDisplay
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns data source for grid of all men items of given event
     *
     * @param Event|null $event
     *
     * @return array
     */
    public function __invoke(?Event $event = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from(MenuItem::class, 'mi')->select('mi');
        if ($event !== null) {
            $qb->where('mi.event = ?1');
            $qb->setParameter(1, $event);
        } else {
            $qb->where('mi.event IS NULL');
        }
        $qb->orderBy('mi.tag', 'ASC');
        $qb->addOrderBy('mi.weight', 'ASC');
        $qb->addOrderBy('mi.content', 'ASC');
        $results = $qb->getQuery()->getResult();
        $output = [];
        /** @var MenuItem $row */
        foreach ($results as $row) {
            if (!isset($output[$row->getTag()])) {
                $output[$row->getTag()] = [];
            }
            $output[$row->getTag()][] = $row;
        }
        return $output;
    }
}
