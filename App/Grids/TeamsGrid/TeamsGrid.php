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

    /** @var int */
    private $eventNumber;

    public function __construct(int $eventNumber, IDataSource $dataSource, DataGridFactory $dataGridFactory)
    {
        parent::__construct();
        $this->dataSource = $dataSource;
        $this->dataGridFactory = $dataGridFactory;
        $this->eventNumber = $eventNumber;
    }

    public function createComponentGrid(string $name): DataGrid
    {
        $grid = $this->dataGridFactory->create($this, $name);

        $grid->setDefaultPerPage(100);
        $grid->setItemsPerPageList([50, 100, 200, 500]);
        $grid->setColumnsHideable();
        $grid->setRememberState(true);

        $grid->setDataSource($this->dataSource);
        $grid->addColumnNumber('id', 'ID')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnNumber('number', 'Číslo')
            ->setSortable()
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
            })
            ->setFilterSelect([
                null => 'Libovolný',
                GameStatus::REGISTERED()->toScalar() => 'Registrován',
                GameStatus::QUALIFIED()->toScalar() => 'Kvalifikován',
                GameStatus::NOT_QUALIFIED()->toScalar() => 'Nekvalifikován',
                GameStatus::PLAYING()->toScalar() => 'Hraje',
            ]);
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
            })
            ->setFilterSelect([
                null => 'Libovolný',
                PaymentStatus::NOT_PAID()->toScalar() => 'Nezaplaceno',
                PaymentStatus::PAID()->toScalar() => 'Zaplaceno',
            ]);

        $grid->addColumnText('name', 'Jméno')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('email', 'E-mail')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('phone', 'Telefon')
            ->setSortable()
            ->setFilterText();
        $grid->addColumnText('phrase', 'Tajná fráze')
            ->setFilterText();


        $grid->addColumnDateTime('registeredAt', 'Registrován')
            ->setFormat('j.n.Y H:i:s')
            ->setDefaultHide(true)
            ->setSortable();
        $grid->addColumnDateTime('lastUpdatedAt', 'Poslední změna')
            ->setFormat('j.n.Y H:i:s')
            ->setSortable();
        $grid->addColumnDateTime('lastLoggedAt', 'Poslední přihlášení')
            ->setFormat('j.n.Y H:i:s')
            ->setSortable();

        $grid->addColumnText('member1', '1. člen')
            ->setRenderer(function (Team $team) {
                $member = $team->getTeamMember(1);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member2', '2. člen')
            ->setRenderer(function (Team $team) {
                $member = $team->getTeamMember(2);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member3', '3. člen')
            ->setRenderer(function (Team $team) {
                $member = $team->getTeamMember(3);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member4', '4. člen')
            ->setRenderer(function (Team $team) {
                $member = $team->getTeamMember(4);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member5', '5. člen')
            ->setRenderer(function (Team $team) {
                $member = $team->getTeamMember(5);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);


        $grid->addExportCsv('CSV export', 'tmou-teams');

        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::VIEW)) {
            $grid->addToolbarButton('Teams:exportNewsletter', 'Export pro newsletter', ['eventNumber' => $this->eventNumber])
                ->setIcon('download')
                ->addAttributes(['title' => 'Export pro newsletter'])
                ->setClass('btn btn-xs btn-default');
        }

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
