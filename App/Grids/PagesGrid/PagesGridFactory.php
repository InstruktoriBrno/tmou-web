<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\PagesGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Ublaboo\DataGrid\DataSource\IDataSource;

class PagesGridFactory
{
    private DataGridFactory $dataGridFactory;

    public function __construct(DataGridFactory $dataGridFactory)
    {
        $this->dataGridFactory = $dataGridFactory;
    }

    public function create(IDataSource $dataSource, ?int $eventNumber): PagesGrid
    {
        return new PagesGrid($dataSource, $eventNumber, $this->dataGridFactory);
    }
}
