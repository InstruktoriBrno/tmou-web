<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindEventsPairsService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given pairs of ID and event name in array as key and value
     *
     * @return array<int, string>
     */
    public function __invoke(): array
    {
        $all = $this->entityManager->getRepository(Event::class)->findBy([], ['number' => 'DESC']);
        $output = [];
        /** @var Event $item */
        foreach ($all as $item) {
            $output[$item->getId()] = $item->getNumber() . '. ' . $item->getName();
        }
        return $output;
    }
}
