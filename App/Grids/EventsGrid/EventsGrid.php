<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\EventsGrid;

use DateTimeImmutable;
use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use InstruktoriBrno\TMOU\Model\Event;
use Nette\Security\User;
use Nette\Utils\Html;
use Contributte\Datagrid\DataGrid;
use Contributte\Datagrid\DataSource\IDataSource;

class EventsGrid extends Control
{
    private IDataSource $dataSource;

    private DataGridFactory $dataGridFactory;

    public function __construct(IDataSource $dataSource, DataGridFactory $dataGridFactory, User $user)
    {
        $this->dataSource = $dataSource;
        $this->dataGridFactory = $dataGridFactory;
        $this->user = $user;
    }

    public function createComponentGrid(string $name): DataGrid
    {
        $grid = $this->dataGridFactory->create($this, $name);

        $grid->setDataSource($this->dataSource);
        $grid->addColumnNumber('id', 'ID')
            ->setFilterText();

        $grid->addColumnText('name', 'Název')
            ->setRenderer(function (Event $item): Html {
                return Html::el()->setText($item->getName());
            });
        $grid->addColumnNumber('number', 'Číslo')
            ->setFilterText();

        $grid->addColumnText('hasQualification', 'Kvalifikace')
            ->setRenderer(function (Event $item): Html {
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
            ->setRenderer(function (Event $item): Html {
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
            ->setRenderer(function (Event $item): Html {
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

        if ($this->user->isAllowed(Resource::ADMIN_EVENTS, Action::CREATE)) {
            $grid->addToolbarButton('Events:add', '')
                ->setIcon('plus')
                ->addAttributes(['title' => 'Přidat ročník'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::EDIT)) {
            $grid->addToolbarButton('Menu:', '')
                ->setIcon('compass')
                ->addAttributes(['title' => 'Položky menu bez ročníku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_PAGES, Action::EDIT)) {
            $grid->addToolbarButton('AdminPages:', '')
                ->setIcon('file')
                ->addAttributes(['title' => 'Stránky bez ročníku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_EVENTS, Action::COPY_CONTENT)) {
            $grid->addToolbarButton('Events:copyContent', '')
                ->setIcon('copy')
                ->addAttributes(['title' => 'Kopírovat obsah ročníku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_PAGES, Action::EDIT)) {
            $grid->addAction('event_pages', '', 'AdminPages:', ['eventNumber' => 'number'])
                ->setIcon('file')
                ->addAttributes(['title' => 'Stránky ročníku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::EDIT)) {
            $grid->addAction('event_menu_items', '', 'Menu:', ['eventNumber' => 'number'])
                ->setIcon('compass')
                ->addAttributes(['title' => 'Položky menu ročníku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::EDIT)) {
            $grid->addAction('event_teams', '', 'Teams:', ['eventNumber' => 'number'])
                ->setIcon('users')
                ->addAttributes(['title' => 'Týmy ročníku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_EVENTS, Action::EDIT)) {
            $grid->addAction('edit', '', 'Events:edit', ['eventId' => 'id'])
                ->setIcon('edit')
                ->addAttributes(['title' => 'Upravit ročník'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_EVENTS, Action::EDIT)) {
            $grid->addAction('qualification', '', 'Events:qualification', ['eventId' => 'id'])
                ->setIcon('award')
                ->addAttributes(['title' => 'Kvalifikace ročníku'])
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
