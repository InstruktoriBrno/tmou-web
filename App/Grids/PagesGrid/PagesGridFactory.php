<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\PagesGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Ublaboo\DataGrid\DataSource\IDataSource;

class PagesGridFactory
{
    /** @var DataGridFactory */
    private $dataGridFactory;

    public function __construct(DataGridFactory $dataGridFactory)
    {
        $this->dataGridFactory = $dataGridFactory;
    }

    public function create(IDataSource $dataSource): PagesGrid
    {
        return new PagesGrid($dataSource, $this->dataGridFactory);
    }
}
