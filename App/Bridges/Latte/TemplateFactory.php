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
    private GameClockService $gameClockService;

    private TeamMacroDataProvider $teamMacroDataProvider;

    private EventMacroDataProvider $eventMacroDataProvider;

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

    public function createTemplate(?UI\Control $control = null, ?string $class = null): UI\Template
    {
        /** @var string[]|false $pagesPresenterServices */
        $pagesPresenterServices = $this->container->findByType(PagesPresenter::class);
        if (!is_array($pagesPresenterServices) || count($pagesPresenterServices) !== 1) {
            throw new \RuntimeException("There must be exactly one instance of " . PagesPresenter::class . " in container.");
        }
        /** @var PagesPresenter $pagesPresenter */
        $pagesPresenter = $this->container->getByName(reset($pagesPresenterServices));
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
