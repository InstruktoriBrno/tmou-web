<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Bridges\Latte;

use InstruktoriBrno\TMOU\Services\Events\EventMacroDataProvider;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use InstruktoriBrno\TMOU\Services\Teams\TeamMacroDataProvider;
use InstruktoriBrno\TMOU\Utils\AbsoluteLinkFilter;
use InstruktoriBrno\TMOU\Utils\AsciiLinkFilter;
use InstruktoriBrno\TMOU\Utils\SmallTexyFilter;
use InstruktoriBrno\TMOU\Utils\TexyFilter;
use Nette\Application\UI;

class TemplateFactory extends \Nette\Bridges\ApplicationLatte\TemplateFactory
{
    /** @var GameClockService */
    private $gameClockService;

    /** @var TeamMacroDataProvider */
    private $teamMacroDataProvider;

    /** @var EventMacroDataProvider */
    private $eventMacroDataProvider;

    private string $basePath;

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

    public function createTemplate(UI\Control $control = null)
    {
        $template = parent::createTemplate($control);
        $template->addFilter('texy', new TexyFilter($this->gameClockService, $this->teamMacroDataProvider, $this->eventMacroDataProvider));
        $template->addFilter('smallTexy', new SmallTexyFilter());
        $template->addFilter('absoluteLink', new AbsoluteLinkFilter($this->basePath));
        $template->addFilter('ascii', new AsciiLinkFilter());
        return $template;
    }
}
