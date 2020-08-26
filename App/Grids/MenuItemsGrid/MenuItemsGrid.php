<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\MenuItemsGrid;

use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use InstruktoriBrno\TMOU\Model\MenuItem;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\IDataSource;
use function assert;

class MenuItemsGrid extends Control
{
    /** @var IDataSource */
    private $dataSource;

    /** @var DataGridFactory */
    private $dataGridFactory;

    /** @var int|null */
    private $eventNumber;

    public function __construct(IDataSource $dataSource, ?int $eventNumber, DataGridFactory $dataGridFactory)
    {
        parent::__construct();
        $this->dataSource = $dataSource;
        $this->dataGridFactory = $dataGridFactory;
        $this->eventNumber = $eventNumber;
    }

    public function createComponentGrid(string $name): DataGrid
    {
        $grid = $this->dataGridFactory->create($this, $name);

        $grid->setDefaultPerPage(50);

        $grid->setDataSource($this->dataSource);
        $grid->addColumnNumber('id', 'ID');

        $grid->addColumnText('tag', 'Tag');
        $grid->addColumnText('label', 'Sekce');
        $grid->addColumnText('content', 'Text');
        $grid->addColumnText('title', 'Tooltip');
        $grid->addColumnText('weight', 'Váha');

        $grid->addColumnText('targetPage', 'Cíl')
            ->setRenderer(function (MenuItem $item): ?Html {
                assert($this->getPresenter() !== null);
                if ($item->getTargetPage() !== null) {
                    $eventNumber = $item->getTargetPage()->getEvent() !== null ? $item->getTargetPage()->getEvent()->getNumber() : null;
                    return Html::el('a')
                        ->href($this->getPresenter()->link('Pages:show', $item->getTargetPage()->getSlug(), $eventNumber))
                        ->setText($item->getContent())
                        ->setAttribute('title', $item->getTitle());
                } elseif ($item->getTargetUrl() !== null) {
                    return Html::el('a')->href($item->getTargetUrl())->setText($item->getContent())->setAttribute('title', $item->getTitle());
                } elseif ($item->getTargetSlug() !== null) {
                    $eventNumber = $item->getTargetEvent() !== null ? $item->getTargetEvent()->getNumber() : null;
                    return Html::el('a')->href($this->getPresenter()->link('Pages:show', $item->getTargetSlug(), $eventNumber))->setText($item->getContent())->setAttribute('title', $item->getTitle());
                }
                return null;
            });
        $grid->addColumnText('forAnonymous', 'Pouze nepřihlášení')
            ->setRenderer(function (MenuItem $item): Html {
                if ($item->isForAnonymous()) {
                    return Html::el('span class="badge badge-xs badge-success"')->setText('Ano');
                }
                return Html::el('span class="badge badge-xs badge-warning"')->setText('Ne');
            });
        $grid->addColumnText('forOrganizators', 'Pro organizátory')
            ->setRenderer(function (MenuItem $item): Html {
                if ($item->isForOrganizators()) {
                    return Html::el('span class="badge badge-xs badge-success"')->setText('Ano');
                }
                return Html::el('span class="badge badge-xs badge-warning"')->setText('Ne');
            });
        $grid->addColumnText('forTeams', 'Pro týmy')
            ->setRenderer(function (MenuItem $item): Html {
                if ($item->isForTeams()) {
                    return Html::el('span class="badge badge-xs badge-success"')->setText('Ano');
                }
                return Html::el('span class="badge badge-xs badge-warning"')->setText('Ne');
            });
        $grid->addColumnDateTime('revealAt', 'Odhalit ve')->setFormat('j. n. Y H:i:s');
        $grid->addColumnDateTime('hideAt', 'Skrýt ve')->setFormat('j. n. Y H:i:s');
        $grid->addColumnDateTime('createdAt', 'Vytvořeno');
        $grid->addColumnDateTime('updatedAt', 'Upraveno');

        if ($this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::CREATE)) {
            $grid->addToolbarButton('Menu:add', '', ['eventNumber' => $this->eventNumber])
                ->setIcon('plus')
                ->addAttributes(['title' => 'Přidat položku menu'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::EDIT)) {
            $grid->addAction('edit', '', 'Menu:edit', ['menuItemId' => 'id', 'eventNumber' => 'event.number'])
                ->setIcon('edit')
                ->addAttributes(['title' => 'Upravit položku menu'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::DELETE)) {
            $grid->addAction('delete', '', 'Menu:delete', ['menuItemId' => 'id'])
                ->setIcon('trash')
                ->addAttributes(['title' => 'Smazat položku menu'])
                ->setClass('btn btn-xs btn-default');
        }

        return $grid;
    }
}
