<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Grids\TeamsGrid;

use InstruktoriBrno\TMOU\Application\UI\Control;
use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Grids\DataGridFactory;
use InstruktoriBrno\TMOU\Model\Team;
use Nette\Security\User;
use Nette\Utils\Html;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\IDataSource;
use \Closure;

class TeamsGrid extends Control
{
    private IDataSource $dataSource;

    private DataGridFactory $dataGridFactory;

    private int $eventNumber;

    /** @var callable(array<int>): void */
    private $changeToPlaying;

    /** @var callable(array<int>): void */
    private $changeToQualified;

    /** @var callable(array<int>): void */
    private $changeToNotQualified;

    /** @var callable(array<int>): void */
    private $changeToRegistered;

    /** @var callable(array<int>): void */
    private $changeAsPaid;

    /** @var callable(array<int>): void */
    private $changeAsNotPaid;

    /** @var callable(array<int>): void */
    private $changeAsPaidAndPlaying;

    /** @var callable(array<int>): void */
    private $allowGameClockChange;

    /** @var callable(array<int>): void */
    private $disableGameClockChange;

    public function __construct(
        int $eventNumber,
        IDataSource $dataSource,
        DataGridFactory $dataGridFactory,
        User $user,
        callable $changeToPlaying,
        callable $changeToQualified,
        callable $changeToNotQualified,
        callable $changeToRegistered,
        callable $changeAsPaid,
        callable $changeAsNotPaid,
        callable $changeAsPaidAndPlaying,
        callable $allowGameClockChange,
        callable $disableGameClockChange
    ) {
        $this->dataSource = $dataSource;
        $this->dataGridFactory = $dataGridFactory;
        $this->eventNumber = $eventNumber;
        $this->changeToPlaying = $changeToPlaying;
        $this->changeToQualified = $changeToQualified;
        $this->changeToNotQualified = $changeToNotQualified;
        $this->changeToRegistered = $changeToRegistered;
        $this->changeAsPaid = $changeAsPaid;
        $this->changeAsNotPaid = $changeAsNotPaid;
        $this->changeAsPaidAndPlaying = $changeAsPaidAndPlaying;
        $this->allowGameClockChange = $allowGameClockChange;
        $this->disableGameClockChange = $disableGameClockChange;
        $this->user = $user;
    }

    public function createComponentGrid(string $name): Datagrid
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
            ->setRenderer(function (Team $team): ?Html {
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
            ->setRenderer(function (Team $team): ?Html {
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
        $grid->addColumnDateTime('paymentPairedAt', 'Datum spárování platby')
            ->setDefaultHide(true);

        $grid->addColumnText('name', 'Jméno')
            ->setRenderer(function (Team $team): Html {
                $output = Html::el();
                $output->addText($team->getName());
                if ($team->canChangeGameTime()) {
                    $output->addHtml('&nbsp;');
                    $output->addHtml(Html::el('span class="fa fa-hourglass-half"')->setAttribute('title', 'Tým může měnit herní čas.'));
                }
                return $output;
            })
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
            ->setRenderer(function (Team $team): ?string {
                $member = $team->getTeamMember(1);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member2', '2. člen')
            ->setRenderer(function (Team $team): ?string {
                $member = $team->getTeamMember(2);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member3', '3. člen')
            ->setRenderer(function (Team $team): ?string {
                $member = $team->getTeamMember(3);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member4', '4. člen')
            ->setRenderer(function (Team $team): ?string {
                $member = $team->getTeamMember(4);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);
        $grid->addColumnText('member5', '5. člen')
            ->setRenderer(function (Team $team): ?string {
                $member = $team->getTeamMember(5);
                if ($member !== null) {
                    return $member->getFullName();
                }
                return null;
            })
            ->setDefaultHide(true);


        $grid->addExportCsv('CSV export*', 'tmou-teams');

        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)) {
            $grid->addToolbarButton('Teams:batchGameStatusChange', 'Hromadná změna stavu', ['eventNumber' => $this->eventNumber])
                ->setIcon('upload')
                ->addAttributes(['title' => 'Hromadná změna stavu'])
                ->setClass('btn btn-xs btn-default');
            $grid->addGroupAction('Nastavit jako hrající')->onSelect[] = Closure::fromCallable($this->changeToPlaying)->bindTo($grid);
            $grid->addGroupAction('Nastavit jako kvalifikovaný')->onSelect[] = Closure::fromCallable($this->changeToQualified)->bindTo($grid);
            $grid->addGroupAction('Nastavit jako registrovaný')->onSelect[] = Closure::fromCallable($this->changeToRegistered)->bindTo($grid);
            $grid->addGroupAction('Nastavit jako nekvalifikovaný')->onSelect[] = Closure::fromCallable($this->changeToNotQualified)->bindTo($grid);
        }
        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_PAYMENT_STATUS_CHANGE)) {
            $grid->addGroupAction('Nastavit jako zaplaceno')->onSelect[] = Closure::fromCallable($this->changeAsPaid)->bindTo($grid);
            $grid->addGroupAction('Nastavit jako nezaplaceno')->onSelect[] = Closure::fromCallable($this->changeAsNotPaid)->bindTo($grid);
        }
        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)
            && $this->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_PAYMENT_STATUS_CHANGE)
        ) {
            $grid->addGroupAction('Nastavit jako zaplaceno & hrající')->onSelect[] = Closure::fromCallable($this->changeAsPaidAndPlaying)->bindTo($grid);
        }
        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::DELEGATE_CHANGE_GAME_CLOCK)) {
            $grid->addGroupAction('Povolit změnu herního času')->onSelect[] = Closure::fromCallable($this->allowGameClockChange)->bindTo($grid);
            $grid->addGroupAction('Zakázat změnu herního času')->onSelect[] = Closure::fromCallable($this->disableGameClockChange)->bindTo($grid);
        }

        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_MAIL)) {
            $grid->addToolbarButton('Teams:batchMail', 'Hromadné mailování', ['eventNumber' => $this->eventNumber])
                ->setIcon('envelope')
                ->addAttributes(['title' => 'Hromadné mailování'])
                ->setClass('btn btn-xs btn-default');
        }

        if ($this->user->isAllowed(Resource::ADMIN_TEAMS, Action::VIEW)) {
            $grid->addToolbarButton('Teams:export', 'Export detailní', ['eventNumber' => $this->eventNumber])
                ->setIcon('download')
                ->addAttributes(['title' => 'Detailní export'])
                ->setClass('btn btn-xs btn-default');
        }

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
