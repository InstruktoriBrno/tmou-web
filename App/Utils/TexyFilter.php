<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use InstruktoriBrno\TMOU\Components\EventQualificationPuzzlesStatisticsControl\EventQualificationPuzzlesStatisticsControlFactory;
use InstruktoriBrno\TMOU\Components\EventQualificationResultsControl\EventQualificationResultsControlFactory;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Presenters\PagesPresenter;
use Nette\Utils\Random;
use function in_array;
use InstruktoriBrno\TMOU\Services\Events\EventMacroDataProvider;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use InstruktoriBrno\TMOU\Services\Teams\TeamMacroDataProvider;
use Latte\Runtime\Html as LatteHtml;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use function ob_start;
use function preg_replace_callback;
use Texy\LineParser;
use Texy\Texy;
use function trim;

class TexyFilter
{
    /** @var Texy */
    private $texy;

    /** @var GameClockService */
    private $gameClockService;

    /** @var TeamMacroDataProvider */
    private $teamMacroDataProvider;

    /** @var EventMacroDataProvider */
    private $eventMacroDataProvider;

    private EventQualificationResultsControlFactory $eventQualificationResultsControlFactory;

    private PagesPresenter $pagesPresenter;

    private EventQualificationPuzzlesStatisticsControlFactory $eventQualificationPuzzlesStatisticsControlFactory;

    public function __construct(
        GameClockService $gameClockService,
        TeamMacroDataProvider $teamMacroDataProvider,
        EventMacroDataProvider $eventMacroDataProvider,
        EventQualificationResultsControlFactory $eventQualificationResultsControlFactory,
        PagesPresenter $pagesPresenter,
        EventQualificationPuzzlesStatisticsControlFactory $eventQualificationPuzzlesStatisticsControlFactory
    ) {
        $this->gameClockService = $gameClockService;
        $this->teamMacroDataProvider = $teamMacroDataProvider;
        $this->eventMacroDataProvider = $eventMacroDataProvider;
        $this->eventQualificationResultsControlFactory = $eventQualificationResultsControlFactory;
        $this->pagesPresenter = $pagesPresenter;
        $this->eventQualificationPuzzlesStatisticsControlFactory = $eventQualificationPuzzlesStatisticsControlFactory;
    }

    public static function getSyntaxHelp(): Html
    {
        $el = Html::el('div');
        $el->addHtml(
            Html::el('pre')->setText(
                '
Nadpis první úrovně (nižší úrovně # * = -)
##########################################

Odstavce se dělají oddělením pomocí prázdného řádku.
 Prosté odřádkování pomocí jedné mezery na začátku řádku.

------

/---div .[wrapper #gallery]

DIV element s CSS třídou a ID.

Odstavec s třídou a ID. .[bigger #big]

Následuje nečíslovaný seznam s CSS třídou a ukázkou základního formátování.

.[spaced]
- **tučný** řez písmo nebo *kurzíva*
- a takto se dělá externí "odkaz":https://www.tmou.cz/
- a takto se dělá interní absolutní "odkaz":/21/page/registration
- a takto se dělá odkaz na stránku v rámci "aktuálního ročníku":page/registration
- u číslovaného seznamu nahraďte - za 1)... 2)...
\---

/---comment
Zakomentováno, nebude ve výstupu.
\---

[* /images/blocks/preview01-spectrometer.jpg .(alternativní text)[image_right clear] *]

Specifické bloky pro TMOU
***************************

Jde především o odhalovací bloky, pokud není podmínka splněna, nebo nějaké z dat chybí, blok se nezobrazí.

/---reveal from 2019-01-01 10:00:00 to 2019-12-31 23:59:59
Objeví se jen ve vymezeném časovém období.
\---

/---reveal from QUALIFICATION_START to GAME_END
Objeví se jen mezi datem začátkem kvalifikace (je-li kvalifikace) a datem konce hry.
\---

/---reveal from 2019-01-01 10:00:00 to -
Objeví se od daného data dále.
\---

/---reveal to registered teams
Objeví se pouze zaregistrovaným týmům.
\---

/---reveal to qualified teams
Objeví se pouze kvalifikovaným týmům.
\---

/---reveal to not_qualified teams
Objeví se pouze nekvalifikovaným týmům.
\---

/---reveal to playing teams
Objeví se pouze hrajícím týmům.
\---

/---hidden
Toto se objeví pouze poté co uživatel zmáčkne tlačítko "Zobrazit"
\---

/---hidden řešení
Toto se objeví pouze poté co uživatel zmáčkne tlačítko "Zobrazit řešení"
\---

/---div .[alert alert-warning]
Toto se zobrazí ve žlutém boxu.
\---

/---div .[alert alert-danger]
Toto se zobrazí v červeném boxu.
\---

/---div .[alert alert-success]
Toto se zobrazí v zeleném boxu.
\---

/---div .[alert alert-info]
Toto se zobrazí ve světle žlutém boxu.
\---

Zanořování specifických bloků pro TMOU
**************************************

Zanořování výše uvedených bloků je možné, ale doporučujeme se mu výhýbat, protože působí potíže a může vést k nepřekládaným výsledkům.
V případě použití je tedy doporučované důsledné testování.

Při zanořování je potřeba dodržovat následující pravidla:
1. Odřádkování (tak jak je vidět výše)
2. Stejná uvozující a ukončující sekvence (tzn. /---- a \----)
3. Vnější bloky musí více - než bloky vnitřní. Minimální počet je 3.
4. Bloky pro odhalení týmům na základě stavu nelze zanořovat do sebe (tým může mít jen jeden stav) do ostatních zanořit lze.


Specifická makra pro TMOU
*************************

Nahradí se příslušnou hodnotou nebo jsou odstraněny, pokud hodnota není (uživatel není přihlášen jako tým či není na stránce spojené s ročníkem).

TMOU:team_id:
TMOU:team_number:
TMOU:team_name:
TMOU:team_phrase:
TMOU:team_vs:
TMOU:team_payment:
TMOU:team_status:

TMOU:event_id:
TMOU:event_name:
TMOU:event_motto:
TMOU:event_number:
TMOU:event_max_teams:
TMOU:event_qualified_teams:
TMOU:event_qualification_period:
TMOU:event_game_period:
TMOU:event_game_start:
TMOU:event_game_end:
TMOU:event_game_start_time:
TMOU:event_game_end_time:
TMOU:event_registration_deadline:
TMOU:event_team_changes_deadline:

Následující makra slouží k zobrazování statistik kvalifikace, pokud je kvalifikace zapnutá. Pokud není, makra se odstraní.

TMOU:component:event_qualification_results: (nekešujte dokud kvalifikace neskončí a výsledky nejsou finální)
TMOU:component:event_qualification_statistics: (nezveřejňujte během hry, nekešujte dokud kvalifikace neskončí a výsledky nejsou finální)

'
            )
        );
        $el->addHtml(Html::el('br'));
        $el->addHtml(
            Html::el('a')->href('https://texy.info/cs/syntax')->setHtml('<br>')->setText('Detailní dokumentace syntaxe')
        );
        return $el;
    }

    public function getTexy(): Texy
    {
        if ($this->texy === null) {
            $this->texy = new Texy();
            $this->texy->headingModule->top = 2; // Start from H2 heading
            if ($this->eventMacroDataProvider->getEventNumber() !== '') {
                $this->texy->linkModule->root = '../../' . $this->eventMacroDataProvider->getEventNumber();
            }
            $this->texy->registerLinePattern(function (LineParser $parser, array $matches): string {
                $section = $matches[1];
                /** @var string $type */
                $type = $matches[2];
                if ($section === 'team') {
                    if ($type === 'id') {
                        return $this->teamMacroDataProvider->getTeamId();
                    }
                    if ($type === 'vs') {
                        return $this->teamMacroDataProvider->getTeamVariableSymbol();
                    }
                    if ($type === 'name') {
                        return $this->teamMacroDataProvider->getTeamName();
                    }
                    if ($type === 'phrase') {
                        return $this->teamMacroDataProvider->getTeamPhrase();
                    }
                    if ($type === 'number') {
                        return $this->teamMacroDataProvider->getTeamNumber();
                    }
                    if ($type === 'status') {
                        return $this->teamMacroDataProvider->getTeamState();
                    }
                    if ($type === 'payment') {
                        return $this->teamMacroDataProvider->getPaymentState();
                    }
                }
                if ($section === 'event') {
                    if ($type === 'id') {
                        return $this->eventMacroDataProvider->getEventId();
                    }
                    if ($type === 'number') {
                        return $this->eventMacroDataProvider->getEventNumber();
                    }
                    if ($type === 'name') {
                        return $this->eventMacroDataProvider->getEventName();
                    }
                    if ($type === 'motto') {
                        return $this->eventMacroDataProvider->getEventMotto();
                    }
                    if ($type === 'max_teams') {
                        return $this->eventMacroDataProvider->getEventTotalTeamCount() ?? 'neomezeně';
                    }
                    if ($type === 'qualified_teams') {
                        return $this->eventMacroDataProvider->getEventQualifiedTeamCount() ?? '';
                    }
                    if ($type === 'qualification_period') {
                        $gameStart = $this->eventMacroDataProvider->getEventQualificationStart();
                        $gameEnd = $this->eventMacroDataProvider->getEventQualificationEnd();
                        if ($gameStart !== null && $gameEnd !== null) {
                            return $gameStart->format('j. n. Y H:i:s') . '&nbsp;&ndash;&nbsp;' . $gameEnd->format('j. n. Y G:i:s');
                        }
                        return 'není';
                    }
                    if ($type === 'game_period') {
                        $gameStart = $this->eventMacroDataProvider->getEventGameStart();
                        $gameEnd = $this->eventMacroDataProvider->getEventGameEnd();
                        if ($gameStart !== null && $gameEnd !== null) {
                            return $gameStart->format('j. n. Y H:i:s') . '&nbsp;&ndash;&nbsp;' . $gameEnd->format('j. n. Y G:i:s');
                        }
                        return ''; // should not happen as both start and end are required
                    }
                    if ($type === 'game_start') {
                        return $this->eventMacroDataProvider->getEventGameStart() !== null ? $this->eventMacroDataProvider->getEventGameStart()->format('j. n. Y G:i') : '';
                    }
                    if ($type === 'game_end') {
                        return $this->eventMacroDataProvider->getEventGameEnd() !== null ? $this->eventMacroDataProvider->getEventGameEnd()->format('j. n. Y G:i') : '';
                    }
                    if ($type === 'registration_deadline') {
                        return $this->eventMacroDataProvider->getRegistrationDeadline() !== null ? $this->eventMacroDataProvider->getRegistrationDeadline()->format('j. n. Y G:i') : 'není otevřena';
                    }
                    if ($type === 'team_changes_deadline') {
                        return $this->eventMacroDataProvider->getChangeDeadline() !== null ? $this->eventMacroDataProvider->getChangeDeadline()->format('j. n. Y G:i') : 'zatím bez omezení';
                    }
                    if ($type === 'game_end') {
                        return $this->eventMacroDataProvider->getEventGameEnd() !== null ? $this->eventMacroDataProvider->getEventGameEnd()->format('j. n. Y G:i') : '';
                    }
                    if ($type === 'game_start_time') {
                        return $this->eventMacroDataProvider->getEventGameStart() !== null ? $this->eventMacroDataProvider->getEventGameStart()->format('G:i') : '';
                    }
                    if ($type === 'game_end_time') {
                        return $this->eventMacroDataProvider->getEventGameEnd() !== null ? $this->eventMacroDataProvider->getEventGameEnd()->format('G:i') : '';
                    }
                }
                return '';
            }, '#TMOU:(team|event)_([a-zA-Z_]*):#uU', 'tmouMacro');
        }

        return $this->texy;
    }

    /**
     * Preprocess reveal blocks as Texy cannot add regexes with "s"
     *
     * /--- reveal from 2019-01-01 10:00:00 to 2019-10-31 10:00:00
     * content
     * \---
     */
    private function preprocessRevealBlocks(string $value): string
    {
        $regex = '#/(--++) *+reveal *+from *+(.*) *+to *+(.*)\\n(.*)$\n\\\\\\1#miuUs';
        $output = preg_replace_callback(
            $regex,
            function ($matches): string {
                $from = trim($matches[2]);
                $to = trim($matches[3]);

                try {
                    $fromDate = null;
                    if ($from === 'GAME_START') {
                        if ($this->eventMacroDataProvider->getEventGameStart() !== null) {
                            $fromDate = $this->eventMacroDataProvider->getEventGameStart();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($from === 'GAME_END') {
                        if ($this->eventMacroDataProvider->getEventGameEnd() !== null) {
                            $fromDate = $this->eventMacroDataProvider->getEventGameEnd();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($from === 'QUALIFICATION_START') {
                        if ($this->eventMacroDataProvider->getEventQualificationStart() !== null) {
                            $fromDate = $this->eventMacroDataProvider->getEventQualificationStart();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($from === 'QUALIFICATION_END') {
                        if ($this->eventMacroDataProvider->getEventQualificationEnd() !== null) {
                            $fromDate = $this->eventMacroDataProvider->getEventQualificationEnd();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($from === 'REGISTRATION_END') {
                        if ($this->eventMacroDataProvider->getRegistrationDeadline() !== null) {
                            $fromDate = $this->eventMacroDataProvider->getRegistrationDeadline();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($from === 'TEAM_CHANGES_END') {
                        if ($this->eventMacroDataProvider->getChangeDeadline() !== null) {
                            $fromDate = $this->eventMacroDataProvider->getChangeDeadline();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($from !== '-' && $from !== '' && !in_array($to, ['GAME_START', 'GAME_END', 'QUALIFICATION_START', 'QUALIFICATION_END', 'REGISTRATION_END', 'TEAM_CHANGES_END'], true)) {
                        $fromDate = Helpers::createDateTimeImmutableFromFormat('Y-m-d H:i:s', $from);
                    }

                    $toDate = null;
                    if ($to === 'GAME_START') {
                        if ($this->eventMacroDataProvider->getEventGameStart() !== null) {
                            $toDate = $this->eventMacroDataProvider->getEventGameStart();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($to === 'GAME_END') {
                        if ($this->eventMacroDataProvider->getEventGameEnd() !== null) {
                            $toDate = $this->eventMacroDataProvider->getEventGameEnd();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($to === 'QUALIFICATION_START') {
                        if ($this->eventMacroDataProvider->getEventQualificationStart() !== null) {
                            $toDate = $this->eventMacroDataProvider->getEventQualificationStart();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($to === 'QUALIFICATION_END') {
                        if ($this->eventMacroDataProvider->getEventQualificationEnd() !== null) {
                            $toDate = $this->eventMacroDataProvider->getEventQualificationEnd();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($to === 'REGISTRATION_END') {
                        if ($this->eventMacroDataProvider->getRegistrationDeadline() !== null) {
                            $toDate = $this->eventMacroDataProvider->getRegistrationDeadline();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($to === 'TEAM_CHANGES_END') {
                        if ($this->eventMacroDataProvider->getChangeDeadline() !== null) {
                            $toDate = $this->eventMacroDataProvider->getChangeDeadline();
                        } else {
                            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException;
                        }
                    }
                    if ($to !== '-' && $to !== '' && !in_array($to, ['GAME_START', 'GAME_END', 'QUALIFICATION_START', 'QUALIFICATION_END', 'REGISTRATION_END', 'TEAM_CHANGES_END'], true)) {
                        $toDate = Helpers::createDateTimeImmutableFromFormat('Y-m-d H:i:s', $to);
                    }
                } catch (\InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException $exception) {
                    return '';
                }

                $current = $this->gameClockService->get();

                if ($fromDate !== null && $current < $fromDate) {
                    return '';
                }
                if ($toDate !== null && $current > $toDate) {
                    return '';
                }
                return $this->preprocessRevealBlocks($matches[4]);
            },
            $value
        );
        return $output ?? '';
    }

    /**
     * Preprocess reveal blocks which are revealed to teams in given status as Texy cannot add regexes with "s"
     *
     * /--- reveal to (registered|qualified|playing) teams
     * content
     * \---
     */
    private function preprocessRevealTeamsBlocks(string $value): string
    {
        $regex = '#/(--++) *+reveal *+to *+(registered|qualified|not_qualified|playing) *+teams\\n(.*)$\n\\\\\\1#miuUs';
        $output = preg_replace_callback(
            $regex,
            function ($matches) {
                $group = $matches[2];
                if (Strings::lower($group) === Strings::lower($this->teamMacroDataProvider->getTeamStateRaw())) {
                    return $matches[3];
                }
                return '';
            },
            $value
        );
        return $output ?? '';
    }

    /**
     * Preprocess hidden blocks which are revealed to user after click on proper button
     *
     * /---hidden
     * content
     * \---
     * @param string $value
     * @return string
     */
    private function preprocessHiddenBlocks(string $value): string
    {
        $regex = '#/(--++) *+hidden(.*)\\n(.*)$\n\\\\\\1#miuUs';
        $output = preg_replace_callback(
            $regex,
            function ($matches): string {
                $key = Random::generate();
                $output = sprintf('<a href="#hidden-%s" class="btn btn-primary collapse-toggle" title="%s" type="button">Zobrazit %s</a>', $key, $matches[2], $matches[2]);
                $output .= sprintf('<div id="hidden-%s" class="collapse">%s</div>', $key, $this->preprocessHiddenBlocks($matches[3]));
                return $output;
            },
            $value
        );
        return $output ?? '';
    }

    /**
     * Preprocess components
     * @param string $value
     * @return string
     */
    private function preprocessComponents(string $value): string
    {
        $regex = '#TMOU:component:event_qualification_results:|TMOU:component:event_qualification_statistics:#miuUs';
        $output = preg_replace_callback(
            $regex,
            function ($matches): string {
                if ($matches[0] === 'TMOU:component:event_qualification_results:') {
                    return $this->renderQualificationResults($this->eventMacroDataProvider->getEvent());
                }
                if ($matches[0] === 'TMOU:component:event_qualification_statistics:') {
                    return $this->renderQualificationStatistics($this->eventMacroDataProvider->getEvent());
                }
                return '';
            },
            $value
        );
        return $output ?? '';
    }

    private function renderQualificationResults(Event $event): string
    {
        $component = $this->eventQualificationResultsControlFactory->create();
        $this->pagesPresenter->addComponent($component, 'qualificationResults');
        ob_start(function (): void {
        });
        $component->renderForEvent($event);
        return (string) ob_get_clean();
    }

    private function renderQualificationStatistics(Event $event): string
    {
        $component = $this->eventQualificationPuzzlesStatisticsControlFactory->create();
        $this->pagesPresenter->addComponent($component, 'qualificationStatistics');
        ob_start(function (): void {
        });
        $component->renderForEvent($event);
        return (string) ob_get_clean();
    }

    public function __invoke(string $value): LatteHtml
    {
        return new LatteHtml($this->getTexy()->process($this->preprocessComponents($this->preprocessHiddenBlocks($this->preprocessRevealBlocks($this->preprocessRevealTeamsBlocks($value))))));
    }
}
