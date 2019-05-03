<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\EventsGrid;

use DateTimeImmutable;
use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use InstruktoriBrno\TMOU\Model\Event;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\IDataSource;

class EventsGrid extends Control
{
    /** @var IDataSource */
    private $dataSource;

    /** @var DataGridFactory */
    private $dataGridFactory;

    public function __construct(IDataSource $dataSource, DataGridFactory $dataGridFactory)
    {
        parent::__construct();
        $this->dataSource = $dataSource;
        $this->dataGridFactory = $dataGridFactory;
    }

    public function createComponentGrid(string $name): DataGrid
    {
        $grid = $this->dataGridFactory->create($this, $name);

        $grid->setDataSource($this->dataSource);
        $grid->addColumnNumber('id', 'ID')
            ->setFilterText();

        $grid->addColumnText('name', 'Název')
            ->setRenderer(function (Event $item) {
                return Html::el('span')
                    ->setAttribute('title', $item->getMotto())
                    ->setText($item->getName());
            });
        $grid->addColumnNumber('number', 'Číslo')
            ->setFilterText();

        $grid->addColumnText('hasQualification', 'Kvalifikace')
            ->setRenderer(function (Event $item) {
                if (!$item->hasQualification()) {
                    return Html::el('span class="badge badge-xs badge-danger"')->setText('Ne');
                }
                if ($item->hasQualificationInterval()) {
                    /** @var DateTimeImmutable $from */
                    $from = $item->getQualificationStart();
                    /** @var DateTimeImmutable $to */
                    $to = $item->getQualificationEnd();
                    return Html::el()
                        ->addText($from->format('j. n. Y H:i'))
                        ->addHtml('<br>')
                        ->addText($to->format('j. n. Y H:i'));
                }
                return Html::el('span class="badge badge-xs badge-default"')->setText('Neurčeno');
            });

        $grid->addColumnText('totalTeamCount', 'Počet týmů')
            ->setRenderer(function (Event $item) {
                $el = Html::el();
                if ($item->hasQualification()) {
                    $el->addText($item->getQualifiedTeamCount());
                    $el->addText('/');
                }

                if ($item->hasNoUpperTeamLimit()) {
                    $el->addHtml('&infin;');
                } else {
                    $el->addText($item->getTotalTeamCount());
                }
                return $el;
            });

        $grid->addColumnText('eventStart', 'Termín')
            ->setRenderer(function (Event $item) {
                if ($item->hasEventInterval()) {
                    /** @var DateTimeImmutable $from */
                    $from = $item->getEventStart();
                    /** @var DateTimeImmutable $to */
                    $to = $item->getEventEnd();
                    return Html::el()
                        ->addText($from->format('j. n. Y H:i'))
                        ->addHtml('<br>')
                        ->addText($to->format('j. n. Y H:i'));
                }
                return Html::el('span class="label label-xs label-default"')->setText('Neurčeno');
            });

        if ($this->user->isAllowed(Resource::ADMIN_EVENTS, Action::EDIT)) {
            $grid->addAction('edit', '', 'Events:edit', ['eventId' => 'id'])
                ->setIcon('edit')
                ->addAttributes(['title' => 'Upravit ročník'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_EVENTS, Action::DELETE)) {
            $grid->addAction('delete', '', 'Events:delete', ['eventId' => 'id'])
                ->setIcon('trash')
                ->addAttributes(['title' => 'Smazat ročník'])
                ->setClass('btn btn-xs btn-default');
        }

        return $grid;
    }
}
