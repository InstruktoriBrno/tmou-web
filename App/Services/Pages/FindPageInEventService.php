<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Page;

class FindPageInEventService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns page with given id or null when no such exists
     *
     * @param string|null $slug
     * @param int|null $eventNumber
     *
     * @return Page|null
     */
    public function __invoke(?string $slug = null, ?int $eventNumber = null): ?Page
    {
        $event = null;
        if ($eventNumber !== null) {
            $event = $this->entityManager->getRepository(Event::class)->findOneBy(['number' => $eventNumber]);
            if (!$event instanceof Event) {
                return null;
            }
        }

        $filters = [
            'event' => $event,
        ];
        if ($slug === null) {
            $filters['isDefault'] = true;
        } else {
            $filters['slug'] = $slug;
        }
        return $this->entityManager->getRepository(Page::class)->findOneBy($filters);
    }
}
