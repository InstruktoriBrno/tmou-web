<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\TeamsGrid;

use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use Nette\Security\User;
use Contributte\Datagrid\DataSource\IDataSource;

class TeamsGridFactory
{
    private DataGridFactory $dataGridFactory;
    private User $user;

    public function __construct(DataGridFactory $dataGridFactory, User $user)
    {
        $this->dataGridFactory = $dataGridFactory;
        $this->user = $user;
    }

    public function create(
        int $eventNumber,
        IDataSource $dataSource,
        callable $changeToPlaying,
        callable $changeToQualified,
        callable $changeToNotQualified,
        callable $changeToRegistered,
        callable $changeAsPaid,
        callable $changeAsNotPaid,
        callable $changeAsPaidAndPlaying,
        callable $allowGameClockChange,
        callable $disableGameClockChange
    ): TeamsGrid {
        return new TeamsGrid(
            $eventNumber,
            $dataSource,
            $this->dataGridFactory,
            $this->user,
            $changeToPlaying,
            $changeToQualified,
            $changeToNotQualified,
            $changeToRegistered,
            $changeAsPaid,
            $changeAsNotPaid,
            $changeAsPaidAndPlaying,
            $allowGameClockChange,
            $disableGameClockChange
        );
    }
}
