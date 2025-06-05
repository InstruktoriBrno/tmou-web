<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindEventsPairsOpenedForDiscussionService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given pairs of ID and event name in array as key and value which are open for discussions
     *
     * @return array<int, string>
     */
    public function __invoke(): array
    {
        $all = $this->entityManager->getRepository(Event::class)->findBy([], ['number' => 'DESC']);
        $output = [];
        /** @var Event $item */
        foreach ($all as $item) {
            if ($item->getEventEnd() !== null && $item->getEventEnd() < new DateTimeImmutable('-6 months')) {
                continue;
            }
            $output[$item->getId()] = $item->getNumber() . '. ' . $item->getName();
        }
        return $output;
    }
}
