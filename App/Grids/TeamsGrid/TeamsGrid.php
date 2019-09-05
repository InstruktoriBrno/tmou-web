<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\TeamsGrid;

use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use InstruktoriBrno\TMOU\Model\Team;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataSource\IDataSource;

class TeamsGrid extends Control
{
    /** @var IDataSource */
    private $dataSource;

    /** @var DataGridFactory */
    private $dataGridFactory;

    public function __construct(IDataSource $dataSource, DataGridFactory $dataGridFactory)
    {
        parent::__construct();
        $this->dataSource = $dataSource;
        $this->dataGridFactory = $dataGridFactory;
    }

    public function createComponentGrid(string $name): DataGrid
    {
        $grid = $this->dataGridFactory->create($this, $name);

        $grid->setDefaultPerPage(100);

        $grid->setDataSource($this->dataSource);
        $grid->addColumnNumber('id', 'ID')
            ->setFilterText();
        $grid->addColumnNumber('number', 'Číslo')
            ->setFilterText();

        $grid->addColumnText('gameStatus', 'Stav')
            ->setRenderer(function (Team $team) {
                $status = $team->getGameStatus();
                if ($status->equals(GameStatus::REGISTERED())) {
                    return Html::el('span class="badge badge-xs badge-info"')->setText('Registrován');
                }
                if ($status->equals(GameStatus::QUALIFIED())) {
                    return Html::el('span class="badge badge-xs badge-warning"')->setText('Kvalifikován');
                }
                if ($status->equals(GameStatus::PLAYING())) {
                    return Html::el('span class="badge badge-xs badge-success"')->setText('Hraje');
                }
                if ($status->equals(GameStatus::NOT_QUALIFIED())) {
                    return Html::el('span class="badge badge-xs badge-danger"')->setText('Nekvalifikován');
                }
                return null;
            });
        $grid->addColumnText('paymentStatus', 'Platba')
            ->setRenderer(function (Team $team) {
                $status = $team->getPaymentStatus();
                if ($status->equals(PaymentStatus::NOT_PAID())) {
                    return Html::el('span class="badge badge-xs badge-danger"')->setText('Ne');
                }
                if ($status->equals(PaymentStatus::PAID()) && $team->getPaymentPairedAt() !== null) {
                    return Html::el('span class="badge badge-xs badge-success"')->setText('Ano')->setAttribute('title', $team->getPaymentPairedAt()->format('j. n. Y H:i:s'));
                }
                return null;
            });

        $grid->addColumnText('name', 'Jméno')
            ->setFilterText();

        $grid->addColumnText('email', 'E-mail')
            ->setFilterText();
        $grid->addColumnText('phone', 'Telefon')
            ->setFilterText();
        $grid->addColumnText('phrase', 'Tajná fráze');

        $grid->addExportCsv('CSV export', 'tmou-teams');

        $grid->addColumnDateTime('registeredAt', 'Registrován');
        $grid->addColumnDateTime('lastLoggedAt', 'Poslední přihlášení');

        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::DELETE)) {
            $grid->addAction('delete', '', 'Teams:delete', ['teamId' => 'id'])
                ->setIcon('trash')
                ->addAttributes(['title' => 'Smazat tým'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::IMPERSONATE)) {
            $grid->addAction('impersonate', '', 'Teams:impersonate', ['teamId' => 'id'])
                ->setIcon('user')
                ->addAttributes(['title' => 'Přihlásit se jako tento tým'])
                ->setClass('btn btn-xs btn-default');
        }

        return $grid;
    }
}
