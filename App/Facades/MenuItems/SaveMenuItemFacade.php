<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\MenuItems;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\MenuItem;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use InstruktoriBrno\TMOU\Services\MenuItems\FindMenuItemService;
use InstruktoriBrno\TMOU\Services\Pages\FindPageService;
use Nette\Utils\ArrayHash;

class SaveMenuItemFacade
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FindMenuItemService */
    private $findMenuItemService;

    /** @var FindPageService */
    private $findPageService;

    /** @var FindEventService */
    private $findEventService;


    public function __construct(
        EntityManagerInterface $entityManager,
        FindMenuItemService $findMenuItemService,
        FindPageService $findPageService,
        FindEventService $findEventService
    ) {
        $this->entityManager = $entityManager;
        $this->findMenuItemService = $findMenuItemService;
        $this->findPageService = $findPageService;
        $this->findEventService = $findEventService;
    }

    /**
     * @param ArrayHash $values
     * @param MenuItem|null $menuItem
     * @param Event|null $event
     *
     * @return MenuItem
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MixedLinkOptionsException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidLinkOptionsException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidUrlException
     * @throws \InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\NoSuchPageException
     * @throws \InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\NoSuchEventException
     */
    public function __invoke(ArrayHash $values, ?MenuItem $menuItem, ?Event $event): MenuItem
    {
        $targetUrl = $targetEvent = $targetPage = $targetSlug = null;
        if ($values->type === 'external') {
            $targetUrl = $values->target_url;
            $targetEvent = null;
            $targetPage = null;
            $targetSlug = null;
        }
        if ($values->type === 'page') {
            $targetUrl = null;
            $targetEvent = null;
            $targetPage = ($this->findPageService)($values->target_page);
            if ($targetPage === null) {
                throw new \InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\NoSuchPageException();
            }
            $targetSlug = null;
        }
        if ($values->type === 'page2') {
            $targetUrl = null;
            $targetEvent = ($this->findEventService)($values->target_event);
            if ($targetEvent === null) {
                throw new \InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\NoSuchEventException;
            }
            $targetPage = null;
            $targetSlug = ReservedSLUG::fromScalar($values->target_slug);
        }

        if ($menuItem !== null) {
            $menuItem->updateDetails(
                $event,
                $values->content === '' ? null : $values->content,
                $values->title === '' ? null : $values->title,
                $values->class === '' ? null : $values->class,
                $values->tag === '' ? null : $values->tag,
                $values->label === '' ? null : $values->label,
                $values->weight,
                $targetPage,
                $targetEvent,
                $targetSlug,
                $targetUrl,
                $values->for_anonymous,
                $values->for_organizators,
                $values->for_teams,
                $values->reveal_at,
                $values->hide_at
            );
        } else {
            $menuItem = new MenuItem(
                $event,
                $values->content === '' ? null : $values->content,
                $values->title === '' ? null : $values->title,
                $values->class === '' ? null : $values->class,
                $values->tag === '' ? null : $values->tag,
                $values->label === '' ? null : $values->label,
                $values->weight,
                $targetPage,
                $targetEvent,
                $targetSlug,
                $targetUrl,
                $values->for_anonymous,
                $values->for_organizators,
                $values->for_teams,
                $values->reveal_at,
                $values->hide_at
            );
        }

        $this->entityManager->persist($menuItem);
        $this->entityManager->flush();

        return $menuItem;
    }
}
