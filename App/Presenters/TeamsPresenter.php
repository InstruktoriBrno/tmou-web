<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Facades\Teams\DeleteTeamFacade;
use InstruktoriBrno\TMOU\Forms\ConfirmFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamBatchMailingFormFactory;
use InstruktoriBrno\TMOU\Grids\TeamsGrid\TeamsGrid;
use InstruktoriBrno\TMOU\Grids\TeamsGrid\TeamsGridFactory;
use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;
use InstruktoriBrno\TMOU\Services\Teams\ExportAllTeamsService;
use InstruktoriBrno\TMOU\Services\Teams\ExportTeamMembersForNewsletterService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsOfEventForDataGridService;
use InstruktoriBrno\TMOU\Services\Teams\TransformBackFromImpersonatedIdentity;
use InstruktoriBrno\TMOU\Services\Teams\TransformToImpersonatedIdentity;
use InstruktoriBrno\TMOU\Utils\TexyFilter;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\Identity;
use Tracy\Debugger;
use Tracy\ILogger;

final class TeamsPresenter extends BasePresenter
{
    /** @var TeamsGridFactory @inject */
    public $teamsGridFactory;

    /** @var FindTeamsOfEventForDataGridService @inject */
    public $findTeamsOfEventForDataGridService;

    /** @var FindEventByNumberService @inject */
    public $findEventServiceByNumber;

    /** @var FindTeamService @inject */
    public $findTeamService;

    /** @var ConfirmFormFactory @inject */
    public $confirmFormFactory;

    /** @var DeleteTeamFacade @inject */
    public $deleteTeamFacade;

    /** @var TransformToImpersonatedIdentity @inject */
    public $transformToImpersonatedIdentity;

    /** @var TransformBackFromImpersonatedIdentity @inject */
    public $transformBackFromImpersonatedIdentity;

    /** @var ExportTeamMembersForNewsletterService @inject */
    public $exportTeamMembersForNewsletter;

    /** @var ExportAllTeamsService @inject */
    public $exportAllTeamsService;

    /** @var TeamBatchMailingFormFactory @inject */
    public $teamBatchMailingFormFactory;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionDefault(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionBatchMail(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
        $this->template->help = TexyFilter::getSyntaxHelp();
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::EDIT) */
    public function actionExport(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->sendResponse(($this->exportAllTeamsService)($event));
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::EDIT) */
    public function actionExportNewsletter(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->sendResponse(($this->exportTeamMembersForNewsletter)($event));
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::DELETE) */
    public function actionDelete(int $teamId): void
    {
        $team = ($this->findTeamService)($teamId);
        if ($team === null) {
            throw new \Nette\Application\BadRequestException("No such team [{$teamId}].");
        }
        $this->template->team = $team;
        $this->template->event = $team->getEvent();
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_TEAMS,InstruktoriBrno\TMOU\Enums\Action::IMPERSONATE) */
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
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        return $this->teamsGridFactory->create($eventNumber, ($this->findTeamsOfEventForDataGridService)($event));
    }

    public function createComponentConfirmForm(): Form
    {
        return $this->confirmFormFactory->create(function (Form $form, $values) {
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
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        return $this->teamBatchMailingFormFactory->create(function (Form $form, $values) {
        }, $event);
    }
}
