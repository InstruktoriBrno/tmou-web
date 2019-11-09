<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Page;
use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;
use InstruktoriBrno\TMOU\Services\Events\FindEventsService;
use InstruktoriBrno\TMOU\Services\MenuItems\FindMenuItemsForDisplayService;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;

/** @property Template $template */
final class Error4xxPresenter extends Presenter
{
    /** @var FindEventsService @inject */
    public $findEventsService;

    /** @var FindEventByNumberService @inject */
    public $findEventByNumberService;

    /** @var GameClockService @inject */
    public $gameClockService;

    /** @var FindMenuItemsForDisplayService @inject */
    public $findMenuItemsForDisplay;

    /** @var int|null|string */
    public static $eventNumber;

    /** @var int */
    public $buildTime;

    public function startup(): void
    {
        parent::startup();
        $this->template->events = ($this->findEventsService)();
        $this->template->currentTime = $this->gameClockService->get();
        $eventNumber = self::$eventNumber !== null ? (int) self::$eventNumber : null;
        if ($eventNumber !== null) {
            $this->template->event = ($this->findEventByNumberService)($eventNumber);
        }

        if ($this->getRequest() !== null && !$this->getRequest()->isMethod(Request::FORWARD)) {
            $this->error();
        }

        $this->template->buildTime = $this->buildTime;

        $this->template->urlPath = $this->getHttpRequest()->getUrl()->getPath();
        if (isset($this->template->event) && $this->template->event instanceof Event) {
            $this->template->menuItems = ($this->findMenuItemsForDisplay)($this->template->event);
        } else {
            $this->template->menuItems = ($this->findMenuItemsForDisplay)(null);
        }
    }

    public function setBuildTime(int $time): void
    {
        $this->buildTime = $time;
    }

    public static function isPageCurrentlySelected(?Page $page, ?string $slug, ?int $eventNumber): bool
    {
        return BasePresenter::isPageCurrentlySelected($page, $slug, $eventNumber);
    }

    public function renderDefault(\Nette\Application\BadRequestException $exception): void
    {
        // load template 403.latte or 404.latte or ... 4xx.latte
        $file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        $this->template->setFile(is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte');
    }
}
