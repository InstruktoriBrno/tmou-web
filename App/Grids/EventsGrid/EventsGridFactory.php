<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\EventsGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Nette\Security\User;
use Contributte\Datagrid\DataSource\IDataSource;

class EventsGridFactory
{
    private DataGridFactory $dataGridFactory;
    private User $user;

    public function __construct(DataGridFactory $dataGridFactory, User $user)
    {
        $this->dataGridFactory = $dataGridFactory;
        $this->user = $user;
    }

    public function create(IDataSource $dataSource): EventsGrid
    {
        return new EventsGrid($dataSource, $this->dataGridFactory, $this->user);
    }
}
