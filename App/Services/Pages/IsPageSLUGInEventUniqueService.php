<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Page;

class IsPageSLUGInEventUniqueService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Checks whether given changed object can be saved with its slug within its event
     * (due to unique constraint)
     *
     * @param Page $page
     *
     * @return bool
     */
    public function __invoke(Page $page): bool
    {
        if ($page->getEvent() === null) {
            $object = $this->entityManager->getRepository(Page::class)->findOneBy(['slug' => $page->getSlug(), 'event' => null]);
        } else {
            $object = $this->entityManager->getRepository(Page::class)->findOneBy(['slug' => $page->getSlug(), 'event' => $page->getEvent()]);
        }

        try {
            if ($object === null || $object->getId() === $page->getId()) {
                return true;
            }
        } catch (\InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException $e) {
            return false;
        }
        return false;
    }
}
