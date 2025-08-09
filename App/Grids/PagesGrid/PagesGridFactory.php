<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\PagesGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Nette\Security\User;
use Contributte\Datagrid\DataSource\IDataSource;

class PagesGridFactory
{
    private DataGridFactory $dataGridFactory;
    private User $user;

    public function __construct(DataGridFactory $dataGridFactory, User $user)
    {
        $this->dataGridFactory = $dataGridFactory;
        $this->user = $user;
    }

    public function create(IDataSource $dataSource, ?int $eventNumber): PagesGrid
    {
        return new PagesGrid($dataSource, $eventNumber, $this->dataGridFactory, $this->user);
    }
}
