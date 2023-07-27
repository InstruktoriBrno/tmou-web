<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Bridges\Latte;

use InstruktoriBrno\TMOU\Components\EventQualificationPuzzlesStatisticsControl\EventQualificationPuzzlesStatisticsControlFactory;
use InstruktoriBrno\TMOU\Components\EventQualificationResultsControl\EventQualificationResultsControlFactory;
use InstruktoriBrno\TMOU\Presenters\PagesPresenter;
use InstruktoriBrno\TMOU\Services\Events\EventMacroDataProvider;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use InstruktoriBrno\TMOU\Services\Teams\TeamMacroDataProvider;
use InstruktoriBrno\TMOU\Utils\AbsoluteLinkFilter;
use InstruktoriBrno\TMOU\Utils\AsciiLinkFilter;
use InstruktoriBrno\TMOU\Utils\SmallTexyFilter;
use InstruktoriBrno\TMOU\Utils\TexyFilter;
use Nette\Application\UI;
use Nette\DI\Container;

class TemplateFactory extends \Nette\Bridges\ApplicationLatte\TemplateFactory
{
    /** @var GameClockService */
    private $gameClockService;

    /** @var TeamMacroDataProvider */
    private $teamMacroDataProvider;

    /** @var EventMacroDataProvider */
    private $eventMacroDataProvider;

    private EventQualificationResultsControlFactory $eventQualificationResultsControlFactory;

    private string $basePath;

    private Container $container;

    private EventQualificationPuzzlesStatisticsControlFactory $eventQualificationPuzzlesStatisticsControlFactory;

    public function injectGameClock(GameClockService $gameClockService): void
    {
        $this->gameClockService = $gameClockService;
    }

    public function injectTeamMacroDataProvider(TeamMacroDataProvider $teamMacroDataProvider): void
    {
        $this->teamMacroDataProvider = $teamMacroDataProvider;
    }

    public function injectEventMacroDataProvider(EventMacroDataProvider $eventMacroDataProvider): void
    {
        $this->eventMacroDataProvider = $eventMacroDataProvider;
    }

    public function injectBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    public function injectEventQualificationResultsControlFactory(EventQualificationResultsControlFactory $eventQualificationResultsControlFactory): void
    {
        $this->eventQualificationResultsControlFactory = $eventQualificationResultsControlFactory;
    }
    public function injectEventQualificationPuzzlesStatisticsFactory(EventQualificationPuzzlesStatisticsControlFactory $eventQualificationPuzzlesStatisticsControlFactory): void
    {
        $this->eventQualificationPuzzlesStatisticsControlFactory = $eventQualificationPuzzlesStatisticsControlFactory;
    }

    public function injectContainer(Container $container): void
    {
        $this->container = $container;
    }

    public function createTemplate(UI\Control $control = null)
    {
        $pagesPresenter = $this->container->getByType(PagesPresenter::class);
        $template = parent::createTemplate($control);
        $template->addFilter('texy', new TexyFilter(
            $this->gameClockService,
            $this->teamMacroDataProvider,
            $this->eventMacroDataProvider,
            $this->eventQualificationResultsControlFactory,
            $pagesPresenter,
            $this->eventQualificationPuzzlesStatisticsControlFactory
        ));
        $template->addFilter('smallTexy', new SmallTexyFilter());
        $template->addFilter('absoluteLink', new AbsoluteLinkFilter($this->basePath));
        $template->addFilter('ascii', new AsciiLinkFilter());
        return $template;
    }
}
