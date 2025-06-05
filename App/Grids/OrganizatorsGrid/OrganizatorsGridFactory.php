<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\OrganizatorsGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Ublaboo\DataGrid\DataSource\IDataSource;

class OrganizatorsGridFactory
{
    private DataGridFactory $dataGridFactory;

    public function __construct(DataGridFactory $dataGridFactory)
    {
        $this->dataGridFactory = $dataGridFactory;
    }

    public function create(IDataSource $dataSource): OrganizatorsGrid
    {
        return new OrganizatorsGrid($dataSource, $this->dataGridFactory);
    }
}
