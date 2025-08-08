<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\OrganizatorsGrid;

use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use InstruktoriBrno\TMOU\Model\Organizator;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\IDataSource;

class OrganizatorsGrid extends Control
{
    private IDataSource $dataSource;

    private DataGridFactory $dataGridFactory;

    public function __construct(IDataSource $dataSource, DataGridFactory $dataGridFactory)
    {
        $this->dataSource = $dataSource;
        $this->dataGridFactory = $dataGridFactory;
    }

    public function createComponentGrid(string $name): Datagrid
    {
        $grid = $this->dataGridFactory->create($this, $name);

        $grid->setDataSource($this->dataSource);
        $grid->addColumnNumber('id', 'ID');
        $grid->addColumnText('role', 'Role')
            ->setRenderer(function (Organizator $organizator) {
                if ($organizator->getRole() !== null) {
                    return $organizator->getRole()->toScalar();
                }
                return null;
            });
        $grid->addColumnText('familyName', 'Příjmení')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('email', 'E-mail')
            ->setFilterText();
        $grid->addColumnText('username', 'Přihlašovací jméno')
            ->setFilterText();
        $grid->addColumnText('keycloakKey', 'Keycloak ID');
        $grid->addColumnDateTime('lastLogin', 'Poslední přihlášení')
            ->setFormat('j.n.Y H:i')
            ->setSortable();

        return $grid;
    }
}
