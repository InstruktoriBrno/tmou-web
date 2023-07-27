<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Facades\Events\DeleteEventFacade;
use InstruktoriBrno\TMOU\Facades\Events\SaveEventFacade;
use InstruktoriBrno\TMOU\Facades\Qualification\DeleteQualificationProgressFacade;
use InstruktoriBrno\TMOU\Facades\Qualification\ImportQualificationFacade;
use InstruktoriBrno\TMOU\Facades\Qualification\QualifyTeamsByQualificationFacade;
use InstruktoriBrno\TMOU\Facades\Qualification\UpdateScoreboardsFacade;
use InstruktoriBrno\TMOU\Facades\System\CopyEventContentFacade;
use InstruktoriBrno\TMOU\Forms\ConfirmFormFactory;
use InstruktoriBrno\TMOU\Forms\CopyEventContentFormFactory;
use InstruktoriBrno\TMOU\Forms\DeleteQualificationProgressFormFactory;
use InstruktoriBrno\TMOU\Forms\EventFormFactory;
use InstruktoriBrno\TMOU\Forms\ImportQualificationFormFactory;
use InstruktoriBrno\TMOU\Grids\EventsGrid\EventsGrid;
use InstruktoriBrno\TMOU\Grids\EventsGrid\EventsGridFactory;
use InstruktoriBrno\TMOU\Services\Events\FindDefaultEventValuesForFormService;
use InstruktoriBrno\TMOU\Services\Events\FindEventForFormService;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use InstruktoriBrno\TMOU\Services\Events\FindEventsForDataGridService;
use InstruktoriBrno\TMOU\Services\Qualification\FindLevelsService;
use InstruktoriBrno\TMOU\Services\Qualification\FindPuzzlesOfEventService;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;
use Tracy\Debugger;
use function implode;

final class EventsPresenter extends BasePresenter
{
    /** @var EventsGridFactory @inject */
    public $eventsGridFactory;

    /** @var FindEventsForDataGridService @inject */
    public $findEventsForDataGrid;

    /** @var EventFormFactory @inject */
    public $eventFormFactory;

    /** @var FindEventService @inject */
    public $findEventService;

    /** @var SaveEventFacade @inject */
    public $saveEventFacade;

    /** @var FindEventForFormService @inject */
    public $findEventForFormService;

    /** @var FindDefaultEventValuesForFormService @inject */
    public $findDefaultEventValuesForFormService;

    /** @var ConfirmFormFactory @inject */
    public $confirmFormFactory;

    /** @var DeleteEventFacade @inject */
    public $deleteEventFacade;

    /** @var CopyEventContentFormFactory @inject */
    public $copyEventContentFormFactory;

    /** @var CopyEventContentFacade @inject */
    public $copyEventContentFacade;

    /** @var ImportQualificationFormFactory @inject */
    public ImportQualificationFormFactory $importQualificationFormFactory;

    /** @var ImportQualificationFacade @inject */
    public ImportQualificationFacade $importQualificationFacade;

    /** @var FindLevelsService @inject */
    public FindLevelsService $findLevelsService;

    /** @var FindPuzzlesOfEventService @inject */
    public FindPuzzlesOfEventService $findPuzzlesOfEventService;

    /** @var DeleteQualificationProgressFormFactory @inject */
    public DeleteQualificationProgressFormFactory $deleteQualificationProgressFormFactory;

    /** @var DeleteQualificationProgressFacade @inject */
    public DeleteQualificationProgressFacade $deleteQualificationProgressFacade;

    /** @var UpdateScoreboardsFacade @inject */
    public UpdateScoreboardsFacade $updateScoreboardsFacade;

    /** @var QualifyTeamsByQualificationFacade @inject */
    public QualifyTeamsByQualificationFacade $qualifyTeamsByQualificationFacade;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::VIEW,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDefault(): void
    {
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::COPY_CONTENT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionCopyContent(): void
    {
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::CREATE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionAdd(): void
    {
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::EDIT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionEdit(int $eventId): void
    {
        $event = ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event [{$eventId}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::DELETE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDelete(int $eventId): void
    {
        $event = ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event [{$eventId}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::EDIT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionQualification(int $eventId): void
    {
        $event = ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event [{$eventId}].");
        }
        $this->template->event = $event;
        $this->template->levels = ($this->findLevelsService)($event);
        $this->template->puzzles = ($this->findPuzzlesOfEventService)($event);
    }

    public function createComponentEventsGrid(): EventsGrid
    {
        return $this->eventsGridFactory->create(($this->findEventsForDataGrid)());
    }

    public function createComponentEventForm(): Form
    {
        $eventId = $this->getParameter('eventId') !== null ? (int) $this->getParameter('eventId') : null;
        $event = $eventId !== null ? ($this->findEventService)($eventId) : null;
        $form = $this->eventFormFactory->create(function (Form $form, ArrayHash $values) use ($event, $eventId): void {
            if (($eventId !== null && ! $this->user->isAllowed(Resource::ADMIN_EVENTS, Action::EDIT))
                || ($eventId === null && ! $this->user->isAllowed(Resource::ADMIN_EVENTS, Action::CREATE))
            ) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');

                return;
            }

            try {
                ($this->saveEventFacade)($values, $event);
                $this->flashMessage('Ročník byl úspěšně uložen.', Flash::SUCCESS);
                $this->redirect('Events:');
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException $e) {
                /** @var TextInput $input */
                $input = $form['name'];
                $input->addError('Název ročníku je příliš dlouhý, maximum je 255 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventNumberException $e) {
                /** @var TextInput $input */
                $input = $form['number'];
                $input->addError('Číslo ročníku není číslo nebo je rovno 0, povoleny jsou kladné hodnoty pro běžné ročníky a záporné hodnoty pro ročníky alternativní.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MissingQualificationIntervalException $e) {
                $form->addError('Termín kvalifikace je neúplný. Nastavte buď oba termíny, nebo žádný (pokud není známý).');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\ExcessQualificationIntervalException $e) {
                $form->addError('Termín kvalifikace nemůže být nastaven u ročníku bez kvalifikace.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventQualificationIntervalException $e) {
                $form->addError('Termín kvalifikace je prázdný interval.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventIntervalException $e) {
                $form->addError('Termín hry je prázdný interval');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MissingQualifiedTeamCountException $e) {
                /** @var TextInput $input */
                $input = $form['qualifiedTeamCount'];
                $input->addError('Vyplňte, prosím, počet kvalifikujících se týmů.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamCountException $e) {
                /** @var TextInput $input */
                $input = $form['totalTeamCount'];
                $input->addError('Celkový počet týmů musí být větší než počet kvalifikujících se týmů.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidRegistrationDeadlineException $e) {
                /** @var TextInput $input */
                $input = $form['registrationDeadline'];
                $input->addError('Deadline registrace musí být před začátkem hry.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidChangeDeadlineException $e) {
                /** @var TextInput $input */
                $input = $form['changeDeadline'];
                $input->addError('Deadline změn týmů musí být před začátkem hry.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\ChangeDeadlineBeforeRegistrationDeadlineException $e) {
                /** @var TextInput $input */
                $input = $form['changeDeadline'];
                $input->addError('Deadline změn týmů musí být alespoň stejný jako deadline pro registraci nebo pozdější.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidPaymentPairingCodeSuffixLengthException $e) {
                /** @var TextInput $input */
                $input = $form['paymentPairingCodeSuffixLength'];
                $input->addError('Číslo určující délku sufixu VS musí být kladné.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentPairingCodePrefixException $e) {
                /** @var TextInput $input */
                $input = $form['paymentPairingCodePrefix'];
                $input->addError('Prefix VS musí být vyplněn pokud je vyplněna délka sufixu VS.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentPairingCodeSuffixLengthException $e) {
                /** @var TextInput $input */
                $input = $form['paymentPairingCodeSuffixLength'];
                $input->addError('Číslo určující délku sufixu VS musí být vyplněno pokud je vyplněn prefix VS.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidAmountException $e) {
                /** @var TextInput $input */
                $input = $form['amount'];
                $input->addError('Startovné musí být nezáporná celá částka v korunách.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MissingAmountException $e) {
                /** @var TextInput $input */
                $input = $form['amount'];
                $input->addError('Pokud je zadán deadline platby, musí být vyplněna částka.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentDeadlineException $e) {
                /** @var TextInput $input */
                $input = $form['paymentDeadline'];
                $input->addError('Pokud je zadána částka, musí být vyplněn deadline platby.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidPaymentPairingCodePrefixException $e) {
                /** @var TextInput $input */
                $input = $form['paymentPairingCodePrefix'];
                $input->addError('Prefix VS musí být nezáporné číslo.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Events\Exceptions\NonUniqueEventNumberException $e) {
                /** @var TextInput $input */
                $input = $form['number'];
                $input->addError('Číslo ročníku musí být unikátní napříč všemi ročníky.');
                return;
            }
        });
        if ($eventId !== null) {
            $values = ($this->findEventForFormService)($eventId);
        } else {
            $values = ($this->findDefaultEventValuesForFormService)();
        }
        $form->setDefaults($values);
        return $form;
    }

    public function createComponentConfirmForm(): Form
    {
        return $this->confirmFormFactory->create(function (Form $form, $values): void {
            /** @var SubmitButton $yes */
            $yes = $form['yes'];
            if ($yes->isSubmittedBy()) {
                if (!$this->user->isAllowed(Resource::ADMIN_EVENTS, Action::DELETE)) {
                    $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                    return;
                }
                try {
                    ($this->deleteEventFacade)((int) $this->getParameter('eventId'));
                } catch (\InstruktoriBrno\TMOU\Facades\Events\Exceptions\EventDeleteFailedException $exception) {
                    Debugger::log($exception);
                    $form->addError('Ročník se nepodařilo smazat.');
                    return;
                }
                $this->flashMessage('Ročník byl úspěšně smazán.', Flash::SUCCESS);
                $this->redirect('Events:');
            } else {
                $this->redirect('Events:');
            }
        });
    }

    public function createComponentCopyEventContentForm(): Form
    {
        return $this->copyEventContentFormFactory->create(function (Form $form, $values): void {
            if (!$this->user->isAllowed(Resource::ADMIN_EVENTS, Action::COPY_CONTENT)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }

            try {
                ($this->copyEventContentFacade)($values->from, $values->to);
            } catch (\InstruktoriBrno\TMOU\Facades\System\Exceptions\NonEmptyEventContentException $exception) {
                $form->addError('V cílovém ročníku již existují nějaké stránky či položky menu.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\System\Exceptions\CannotCopyFromToException $exception) {
                $form->addError('Zdrojový a cílový ročník nemohou být stejné.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\System\Exceptions\NoSuchEventException $exception) {
                $form->addError('Zdrojový nebo cílový ročník nebyl nalezen, opakujte akci a v případě selhání kontaktujte správce.');
                return;
            }
            $this->flashMessage('Stránky a položky menu vybraného ročníku byly úspěšně zkopírovány.', Flash::SUCCESS);
            $this->redirect('Events:');
        });
    }

    public function createComponentImportQualificationForm(): Form
    {
        $eventId = $this->getParameter('eventId') !== null ? (int) $this->getParameter('eventId') : null;
        return $this->importQualificationFormFactory->create(function (Form $form, $values) use ($eventId): void {
            if (!$this->user->isAllowed(Resource::ADMIN_EVENTS, Action::EDIT)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }

            try {
                ($this->importQualificationFacade)($eventId, $values->specification);
            } catch (\InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\InvalidXmlSchemaException $exception) {
                $form->addError(Html::el()->setHtml("XML dokument obsahuje následující chyby:<br><br>" . implode("<br><br>", $exception->getErrors())));
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\System\Exceptions\NoSuchEventException $exception) {
                $form->addError('Daný ročník neexistuje, opakujte akci a v případě selhání kontaktujte správce.');
                return;
            }
            $this->flashMessage('Kvalifikace byla importována', Flash::SUCCESS);
            $this->redirect('this');
        },
        '/assets/schemas/qualification.example.xml',
        '/assets/schemas/qualification.xsd');
    }

    public function createComponentDeleteQualificationProgressForm(): Form
    {
        $eventId = $this->getParameter('eventId') !== null ? (int) $this->getParameter('eventId') : null;
        $event = $eventId !== null ? ($this->findEventService)($eventId) : null;
        if ($event === null) {
            throw new \Nette\Application\BadRequestException('Event not found');
        }
        return $this->deleteQualificationProgressFormFactory->create(function (Form $form, $values) use ($event): void {
            if (!$this->user->isAllowed(Resource::ADMIN_EVENTS, Action::EDIT)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }

            try {
                ($this->deleteQualificationProgressFacade)($event, $values->scope);
            } catch (\InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\NoSuchTeamInEventException $exception) {
                $form->addError('Vybraný tým neexistuje v daném ročníku, opakujte akci a v případě selhání kontaktujte správce.');
                return;
            }
            if ($values->scope === null) {
                $this->flashMessage('Průběh kvalifikace všech týmů v ročníku Y byl smazán.', Flash::SUCCESS);
            } else {
                $this->flashMessage('Průběh kvalifikace týmu X byl smazán.', Flash::SUCCESS);
            }

            $this->redirect('this');
        }, $event);
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::EDIT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function handleUpdateEventQualification(int $eventId): void
    {
        $event =  ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException('Event not found');
        }
        ($this->updateScoreboardsFacade)($event);
        $this->flashMessage('Aktualizace proběhla úspěšně.', Flash::SUCCESS);
        $this->redirect('this');
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::EDIT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function handleQualifyTeams(int $eventId): void
    {
        $event =  ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException('Event not found');
        }
        [$qualified, $notQualified, $intact] = ($this->qualifyTeamsByQualificationFacade)($event);
        $this->flashMessage("Aktualizace proběhla úspěšně, kvalifikováno: {$qualified}, nekvalifikováno: {$notQualified}, zachován předchozí stav: {$intact}.", Flash::SUCCESS);
        $this->redirect('this');
    }
}
