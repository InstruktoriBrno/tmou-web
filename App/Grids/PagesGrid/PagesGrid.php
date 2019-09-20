<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\PagesGrid;

use DateTimeImmutable;
use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use InstruktoriBrno\TMOU\Model\Page;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\IDataSource;

class PagesGrid extends Control
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

        $grid->setDataSource($this->dataSource);
        $grid->addColumnNumber('id', 'ID')
            ->setFilterText();

        $grid->addColumnText('slug', 'SLUG')
            ->setFilterText();

        $grid->addColumnText('title', 'Název')
            ->setRenderer(function (Page $page) {
                $output = Html::el();
                $output->addText($page->getTitle());
                if ($page->isDefault()) {
                    $output->addHtml(Html::el('br'));
                    $output->addHtml(Html::el('span class="badge badge-xs badge-default"')->setText('Výchozí'));
                }
                return $output;
            });

        $grid->addColumnText('hidden', 'Skrývaný')
            ->setRenderer(function (Page $item) {
                if (!$item->isHidden()) {
                    return Html::el('span class="badge badge-xs badge-default"')->setText('Ne');
                }
                return Html::el('span class="badge badge-xs badge-success"')->setText('Ano');
            });

        $grid->addColumnDateTime('revealAt', 'Odhalit v');

        $grid->addColumnText('visible', 'Zobrazený')
            ->setRenderer(function (Page $item) {
                if ($item->isRevealed(new DateTimeImmutable())) {
                    return Html::el('span class="badge badge-xs badge-success"')->setText('Ano');
                }
                return Html::el('span class="badge badge-xs badge-warning"')->setText('Ne');
            });

        $grid->addColumnDateTime('lastUpdatedAt', 'Naposledy upraveno');

        if ($this->user->isAllowed(Resource::ADMIN_PAGES, Action::CREATE)) {
            $grid->addToolbarButton('AdminPages:add', '', ['eventNumber' => $this->eventNumber])
                ->setIcon('plus')
                ->addAttributes(['title' => 'Přidat stránku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::PAGES, Action::VIEW)) {
            $grid->addAction('show', '', 'Pages:show', ['eventNumber' => 'event.number', 'slug' => 'slug'])
                ->setIcon('search')
                ->addAttributes(['title' => 'Zobrazit stránku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_PAGES, Action::EDIT)) {
            $grid->addAction('edit', '', 'AdminPages:edit', ['pageId' => 'id'])
                ->setIcon('edit')
                ->addAttributes(['title' => 'Upravit stránku'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_PAGES, Action::DELETE)) {
            $grid->addAction('delete', '', 'AdminPages:delete', ['pageId' => 'id'])
                ->setIcon('trash')
                ->addAttributes(['title' => 'Smazat stránku'])
                ->setClass('btn btn-xs btn-default');
        }

        return $grid;
    }
}
