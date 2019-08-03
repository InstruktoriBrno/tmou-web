<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Pages;

use InstruktoriBrno\TMOU\Services\Pages\DeletePageService;

class DeletePageFacade
{
    /** @var DeletePageService */
    private $deleteEventService;

    public function __construct(
        DeletePageService $deleteEventService
    ) {
        $this->deleteEventService = $deleteEventService;
    }

    /**
     * Takes care about complete deletion of page with given ID
     *
     * @param int $pageId
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\PageDeleteFailedException
     */
    public function __invoke(int $pageId): void
    {
        try {
            ($this->deleteEventService)($pageId);
        } catch (\InstruktoriBrno\TMOU\Services\Pages\Exceptions\PageDeleteFailedException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\PageDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
