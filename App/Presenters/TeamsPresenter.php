<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Facades\Events\MatchPaymentsFacade;
use InstruktoriBrno\TMOU\Facades\Teams\BatchGameStatusChangeFacade;
use InstruktoriBrno\TMOU\Facades\Teams\BatchMailTeamsFacade;
use InstruktoriBrno\TMOU\Facades\Teams\DeleteTeamFacade;
use InstruktoriBrno\TMOU\Forms\ConfirmFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamBatchGameStatusChangeFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamBatchMailingFormFactory;
use InstruktoriBrno\TMOU\Grids\TeamsGrid\TeamsGrid;
use InstruktoriBrno\TMOU\Grids\TeamsGrid\TeamsGridFactory;
use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;
use InstruktoriBrno\TMOU\Services\Teams\ChangeTeamsCanChangeGameTimeService;
use InstruktoriBrno\TMOU\Services\Teams\ChangeTeamsGameStatusService;
use InstruktoriBrno\TMOU\Services\Teams\ChangeTeamsPaymentStatusService;
use InstruktoriBrno\TMOU\Services\Teams\ExportAllTeamsService;
use InstruktoriBrno\TMOU\Services\Teams\ExportTeamMembersForNewsletterService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsOfEventForDataGridService;
use InstruktoriBrno\TMOU\Services\Teams\TransformBackFromImpersonatedIdentity;
use InstruktoriBrno\TMOU\Services\Teams\TransformToImpersonatedIdentity;
use InstruktoriBrno\TMOU\Utils\TexyFilter;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\Identity;
use Tracy\Debugger;
use Tracy\ILogger;
use Contributte\Datagrid\DataGrid;
use function array_key_exists;
use function assert;
use function sprintf;

final class TeamsPresenter extends BasePresenter
{
    #[Inject]
    public TeamsGridFactory $teamsGridFactory;

    #[Inject]
    public FindTeamsOfEventForDataGridService $findTeamsOfEventForDataGridService;

    #[Inject]
    public FindEventByNumberService $findEventServiceByNumber;

    #[Inject]
    public FindTeamService $findTeamService;

    #[Inject]
    public ConfirmFormFactory $confirmFormFactory;

    #[Inject]
    public DeleteTeamFacade $deleteTeamFacade;

    #[Inject]
    public TransformToImpersonatedIdentity $transformToImpersonatedIdentity;

    #[Inject]
    public TransformBackFromImpersonatedIdentity $transformBackFromImpersonatedIdentity;

    #[Inject]
    public ExportTeamMembersForNewsletterService $exportTeamMembersForNewsletter;

    #[Inject]
    public ExportAllTeamsService $exportAllTeamsService;

    #[Inject]
    public TeamBatchMailingFormFactory $teamBatchMailingFormFactory;

    #[Inject]
    public BatchMailTeamsFacade $batchMailTeamsFacade;

    #[Inject]
    public TeamBatchGameStatusChangeFormFactory $teamBatchGameStatusChangeFormFactory;

    #[Inject]
    public BatchGameStatusChangeFacade $batchGameStatusChangeFacade;

    #[Inject]
    public ChangeTeamsGameStatusService $changeTeamsGameStatusService;

    #[Inject]
    public ChangeTeamsPaymentStatusService $changeTeamsPaymentStatusService;

    #[Inject]
    public ChangeTeamsCanChangeGameTimeService $changeTeamsCanChangeGameTimeService;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::VIEW,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDefault(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [{$eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::BATCH_MAIL,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionBatchMail(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [{$eventNumber}].");
        }
        $this->template->event = $event;
        $this->template->help = TexyFilter::getSyntaxHelp();
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::BATCH_GAME_STATUS_CHANGE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionBatchGameStatusChange(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [{$eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::EDIT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionExport(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number {$eventNumber}].");
        }
        $this->sendResponse(($this->exportAllTeamsService)($event));
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::EDIT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionExportNewsletter(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [{$eventNumber}].");
        }
        $this->sendResponse(($this->exportTeamMembersForNewsletter)($event));
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::DELETE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDelete(int $teamId): void
    {
        $team = ($this->findTeamService)($teamId);
        if ($team === null) {
            throw new \Nette\Application\BadRequestException("No such team [{$teamId}].");
        }
        $this->template->team = $team;
        $this->template->event = $team->getEvent();
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::VIEW,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionPayments(bool $emptyNow = false): void
    {
        $path = __DIR__ . '/../../payments/' . MatchPaymentsFacade::RUNS;
        if ($emptyNow) {
            file_put_contents($path, '');
            $this->flashMessage('Log byl úspěšně vyprázdněn.', Flash::SUCCESS);
            $this->redirect('this', false);
        }
        $content = @file_get_contents($path);
        $this->template->lines = array_reverse(explode("\n", (string) $content));
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::IMPERSONATE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionImpersonate(int $teamId): void
    {
        $team = ($this->findTeamService)($teamId);
        if ($team === null) {
            throw new \Nette\Application\BadRequestException("No such team [{$teamId}].");
        }
        $identity = $team->toIdentity();
        try {
            $newIdentity = ($this->transformToImpersonatedIdentity)($identity);
            $this->user->login($newIdentity);
            $this->flashMessage(sprintf(
                'Byli jste impersonifikováni jako tým %s v %s. ročníku. Můžete dělat veškeré akce jako tento tým!',
                $team->getName(),
                $team->getEvent()->getNumber()
            ), Flash::SUCCESS);
            $this->redirect('Pages:show', null, $team->getEvent()->getNumber());
        } catch (\InstruktoriBrno\TMOU\Services\Teams\Exceptions\ImpersonationException | \Nette\Security\AuthenticationException $e) {
            Debugger::log($e, ILogger::EXCEPTION);
            $this->redirect('Teams:', $team->getEvent()->getNumber());
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::DEIMPERSONATE) */
    public function actionDeimpersonate(): void
    {
        $identity = $this->user->getIdentity();
        assert($identity instanceof Identity);
        try {
            $newIdentity = ($this->transformBackFromImpersonatedIdentity)($identity);
            $this->user->login($newIdentity);
            $this->flashMessage(sprintf(
                'Impersonifikace byla ukončena. Nyní jste opět přihlášeni jako %s %s!',
                $newIdentity->getData()['givenName'],
                $newIdentity->getData()['familyName']
            ), Flash::SUCCESS);
            $this->redirect('Teams:', $identity->getData()['eventNumber']);
        } catch (\InstruktoriBrno\TMOU\Services\Teams\Exceptions\DeimpersonationException | \Nette\Security\AuthenticationException $e) {
            Debugger::log($e, ILogger::EXCEPTION);
            throw new \Nette\Application\BadRequestException("Cannot deimpersonate.", 403);
        }
    }

    public function createComponentTeamsGrid(): TeamsGrid
    {
        $eventNumber = (int) $this->getParameter('eventNumber');
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [{$eventNumber}].");
        }
        $presenter = $this;
        $changeToPlaying = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsGameStatusService)($ids, GameStatus::PLAYING());
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        $changeToQualified = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsGameStatusService)($ids, GameStatus::QUALIFIED());
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        $changeToNotQualified = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsGameStatusService)($ids, GameStatus::NOT_QUALIFIED());
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        $changeToRegistered = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsGameStatusService)($ids, GameStatus::REGISTERED());
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        $changeAsPaid = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_PAYMENT_STATUS_CHANGE)) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsPaymentStatusService)($ids, true);
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        $changeAsNotPaid = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_PAYMENT_STATUS_CHANGE)) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsPaymentStatusService)($ids, false);
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        $changeAsPaidAndPlaying = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_PAYMENT_STATUS_CHANGE)
                || !$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)
            ) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsGameStatusService)($ids, GameStatus::PLAYING());
            $changed2 = ($presenter->changeTeamsPaymentStatusService)($ids, true);
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno na hrající a %d týmů bylo úspěně změněno na zaplaceno.', $changed, $changed2), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        $allowGameClockChange = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::CHANGE_GAME_CLOCK)
                || !$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::DELEGATE_CHANGE_GAME_CLOCK)
            ) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsCanChangeGameTimeService)($ids, true);
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };

        $disableGameClockChange = function (array $ids) use ($presenter) {
            if (!$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::CHANGE_GAME_CLOCK)
                || !$presenter->user->isAllowed(Resource::ADMIN_TEAMS, Action::DELEGATE_CHANGE_GAME_CLOCK)
            ) {
                $presenter->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                $presenter->redrawControl('flashes');
                return null;
            }
            $changed = ($presenter->changeTeamsCanChangeGameTimeService)($ids, false);
            if ($presenter->isAjax()) {
                $presenter->flashMessage(sprintf('%d týmů bylo úspěšně změněno.', $changed), Flash::SUCCESS);
                $presenter->redrawControl('flashes');
                $form = $this->getComponent('filter');
                /** @var mixed $values */
                $values = $form->getValues(true);
                $filter = array_key_exists('filter', $values) ? ($values['filter']) : [];
                /** @var DataGrid $grid */
                $grid = $this;
                $grid->setFilter($filter);
                $grid->reload();
            } else {
                $presenter->redirect('this');
            }
        };
        return $this->teamsGridFactory->create(
            $eventNumber,
            ($this->findTeamsOfEventForDataGridService)($event),
            $changeToPlaying,
            $changeToQualified,
            $changeToNotQualified,
            $changeToRegistered,
            $changeAsPaid,
            $changeAsNotPaid,
            $changeAsPaidAndPlaying,
            $allowGameClockChange,
            $disableGameClockChange,
        );
    }

    public function createComponentConfirmForm(): Form
    {
        return $this->confirmFormFactory->create(function (Form $form, $values): void {
            $teamId = (int) $this->getParameter('teamId');
            $team = ($this->findTeamService)($teamId);
            assert($team !== null);

            /** @var SubmitButton $yes */
            $yes = $form['yes'];
            if ($yes->isSubmittedBy()) {
                if (!$this->user->isAllowed(Resource::ADMIN_TEAMS, Action::DELETE)) {
                    $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                    return;
                }
                try {
                    ($this->deleteTeamFacade)($teamId);
                } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TeamDeleteFailedException $exception) {
                    Debugger::log($exception);
                    $form->addError('Tým se nepodařilo smazat.');
                    return;
                }
                $this->flashMessage('Tým byl úspěšně smazán.', Flash::SUCCESS);
                $this->redirect('Teams:', $team->getEvent()->getNumber());
            } else {
                $this->redirect('Teams:', $team->getEvent()->getNumber());
            }
        });
    }

    public function createComponentBatchMailing(): Form
    {
        $eventNumber = (int) $this->getParameter('eventNumber');
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [{$eventNumber}].");
        }
        assert($this->getRequest() !== null);
        $filterStates = $this->getRequest()->getPost('filterStates');
        $filterPaymentStates = $this->getRequest()->getPost('filterPaymentStates');

        return $this->teamBatchMailingFormFactory->create(function (Form $form, $values) use ($event): void {
            if (!$this->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_MAIL)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }

            $filter = $form['filter'];
            if ($filter instanceof SubmitButton && $filter->isSubmittedBy()) {
                return;
            }

            /** @var SubmitButton $input */
            $input = $form['preview'];
            $previewOnly = false;
            if ($input->isSubmittedBy()) {
                $previewOnly = true;
            }
            try {
                $sent = ($this->batchMailTeamsFacade)($values, $event, $previewOnly);
                $this->flashMessage(sprintf('Hromadný e-mail byl úspěšně rozeslán následující počet adres: %d.', $sent), Flash::SUCCESS);
                $this->redirect('this');
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\PreviewException $previewException) {
                $this->template->previews = $previewException->getData();
                $this->flashMessage('Níže můžete vidět až 10 náhledů z prvních mailů.', Flash::INFO);
            } catch (\Nette\Application\AbortException $exception) {
                throw $exception;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\ReachedLimitException $exception) {
                $form->addError(
                    'Hromadné odeslání selhalo z důvodu dosažení limitu MailGun API. K selhání došlo po úspěšném odeslání následujícímu počtu prvních adresátů: ' . $exception->getMessage() . '.'
                );
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\UnknownSendingException $exception) {
                $form->addError(
                    'Hromadné odeslání selhalo. Více informací je uloženo v logu webu. K selhání došlo po úspěšném odeslání následujícímu počtu prvních adresátů: ' . $exception->getMessage() . '.'
                );
            } catch (\Exception $exception) {
                Debugger::log($exception, ILogger::EXCEPTION);
                $form->addError('Hromadné odeslání selhalo. Více informací je uloženo v logu webu.');
            }
        }, $event, $filterStates, $filterPaymentStates);
    }

    public function createComponentBatchGameStatusChange(): Form
    {
        $eventNumber = (int) $this->getParameter('eventNumber');
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [{$eventNumber}].");
        }
        return $this->teamBatchGameStatusChangeFormFactory->create(function (Form $form, $values) use ($event): void {
            if (!$this->user->isAllowed(Resource::ADMIN_TEAMS, Action::BATCH_GAME_STATUS_CHANGE)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }
            try {
                [$changed, $other] = ($this->batchGameStatusChangeFacade)($values, $event);
                $this->flashMessage(sprintf('Hromadný změna stavu proběhla úspěšně, změněno bylo %s týmů dle souboru a %d zbylých dle vašeho požadavku.', $changed, $other), Flash::SUCCESS);
                $this->redirect('this');
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\UploadCouldNotBeenProcessedException $exception) {
                $form->addError('Nahrání souboru selhalo.');
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException $exception) {
                $form->addError(sprintf('Neimportováno. Tým s ID %s nebyl nalezen.', $exception->getMessage()));
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchGameStatusException $exception) {
                $form->addError(sprintf('Neimportováno. Stav %s nebyl nalezen.', $exception->getMessage()));
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\MalformedFormatException $exception) {
                $form->addError(sprintf('Neimportováno. Řádek "%s" nemá správný počet sloupců.', $exception->getMessage()));
            } catch (\Nette\Application\AbortException $exception) {
                throw $exception;
            } catch (\Exception $exception) {
                Debugger::log($exception, ILogger::EXCEPTION);
                $form->addError('Hromadná změna stavu selhala. Více informací je uloženo v logu webu.');
            }
        }, $event);
    }
}
