<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\EventsGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Ublaboo\DataGrid\DataSource\IDataSource;

class EventsGridFactory
{
    /** @var DataGridFactory */
    private $dataGridFactory;

    public function __construct(DataGridFactory $dataGridFactory)
    {
        $this->dataGridFactory = $dataGridFactory;
    }

    public function create(IDataSource $dataSource): EventsGrid
    {
        return new EventsGrid($dataSource, $this->dataGridFactory);
    }
}
