<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Page;

class FindPagesPairsService
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
     * @return array<string, array<int, string>>
     */
    public function __invoke(): array
    {
        $all = $this->entityManager->getRepository(Page::class)->findBy([], ['event' => 'DESC']);
        $output = [];
        /** @var Page $item */
        foreach ($all as $item) {
            $evenLabel = '' . ($item->getEvent() !== null ? $item->getEvent()->getNumber() . '. ročník' : 'Mimo ročníky');
            $output[$evenLabel][$item->getId()] = $item->getTitle();
        }
        return $output;
    }
}
