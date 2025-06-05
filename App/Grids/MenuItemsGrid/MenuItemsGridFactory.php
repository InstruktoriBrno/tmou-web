<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\MenuItemsGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Ublaboo\DataGrid\DataSource\IDataSource;

class MenuItemsGridFactory
{
    private DataGridFactory $dataGridFactory;

    public function __construct(DataGridFactory $dataGridFactory)
    {
        $this->dataGridFactory = $dataGridFactory;
    }

    public function create(IDataSource $dataSource, ?int $eventNumber): MenuItemsGrid
    {
        return new MenuItemsGrid($dataSource, $eventNumber, $this->dataGridFactory);
    }
}
