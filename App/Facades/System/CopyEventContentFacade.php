<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\System;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use InstruktoriBrno\TMOU\Services\MenuItems\FindMenuItemsInEventService;
use InstruktoriBrno\TMOU\Services\Pages\FindPagesInEventService;

class CopyEventContentFacade
{
    private EntityManagerInterface $entityManager;

    private FindEventService $findEventService;

    private FindPagesInEventService $findPagesInEventService;

    private FindMenuItemsInEventService $findMenuItemsInEventService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindEventService $findEventService,
        FindPagesInEventService $findPagesInEventService,
        FindMenuItemsInEventService $findMenuItemsInEventService
    ) {

        $this->entityManager = $entityManager;
        $this->findEventService = $findEventService;
        $this->findPagesInEventService = $findPagesInEventService;
        $this->findMenuItemsInEventService = $findMenuItemsInEventService;
    }

    public function __invoke(int $eventIdFrom, int $eventIdTo): void
    {
        if ($eventIdFrom === $eventIdTo) {
            throw new \InstruktoriBrno\TMOU\Facades\System\Exceptions\CannotCopyFromToException;
        }

        $eventFrom = ($this->findEventService)($eventIdFrom);
        $eventTo = ($this->findEventService)($eventIdTo);
        if ($eventFrom === null || $eventTo === null) {
            throw new \InstruktoriBrno\TMOU\Facades\System\Exceptions\NoSuchEventException;
        }


        $pagesInTarget = ($this->findPagesInEventService)($eventTo);
        $menuItemsInTarget = ($this->findMenuItemsInEventService)($eventTo);
        if (count($pagesInTarget) !== 0 || count($menuItemsInTarget) !== 0) {
            throw new \InstruktoriBrno\TMOU\Facades\System\Exceptions\NonEmptyEventContentException;
        }

        // Copy all pages
        $pages = ($this->findPagesInEventService)($eventFrom);
        $slugs = [];
        foreach ($pages as $page) {
            $newPage = clone $page;
            $newPage->switchToEvent($eventTo);
            $slugs[$newPage->getSlug()] = $newPage;
            $this->entityManager->persist($newPage);
        }

        // Copy all menu items
        $menuItems = ($this->findMenuItemsInEventService)($eventFrom);
        foreach ($menuItems as $menuItem) {
            $newMenuItem = clone $menuItem;
            $newTargetPage = null;
            if ($newMenuItem->getTargetPage() !== null) {
                $newTargetPage = $slugs[$newMenuItem->getTargetPage()->getSlug()] ?? null;
            }
            $newMenuItem->switchToEvent($eventTo, $newTargetPage);
            $this->entityManager->persist($newMenuItem);
        }

        $this->entityManager->flush();
    }
}
