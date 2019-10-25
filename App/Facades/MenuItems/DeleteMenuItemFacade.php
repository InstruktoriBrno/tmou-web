<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\MenuItems;

use InstruktoriBrno\TMOU\Services\MenuItems\DeleteMenuItemService;

class DeleteMenuItemFacade
{
    /** @var DeleteMenuItemService */
    private $deleteMenuItemService;

    public function __construct(
        DeleteMenuItemService $deleteMenuItemService
    ) {
        $this->deleteMenuItemService = $deleteMenuItemService;
    }

    /**
     * Takes care about complete deletion of menu item with given ID
     *
     * @param int $menuItemId
     *
     * @throws \InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\MenuItemDeleteFailedException
     */
    public function __invoke(int $menuItemId): void
    {
        try {
            ($this->deleteMenuItemService)($menuItemId);
        } catch (\InstruktoriBrno\TMOU\Services\MenuItems\Exceptions\MenuItemDeleteFailedException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\MenuItemDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
