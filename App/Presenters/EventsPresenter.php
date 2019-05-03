<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Facades\Events\DeleteEventFacade;
use InstruktoriBrno\TMOU\Facades\Events\SaveEventFacade;
use InstruktoriBrno\TMOU\Forms\ConfirmFormFactory;
use InstruktoriBrno\TMOU\Forms\EventFormFactory;
use InstruktoriBrno\TMOU\Grids\EventsGrid\EventsGrid;
use InstruktoriBrno\TMOU\Grids\EventsGrid\EventsGridFactory;
use InstruktoriBrno\TMOU\Services\Events\FindDefaultEventValuesForFormService;
use InstruktoriBrno\TMOU\Services\Events\FindEventForFormService;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use InstruktoriBrno\TMOU\Services\Events\FindEventsForDataGridService;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\ArrayHash;

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

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionDefault(): void
    {
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::CREATE) */
    public function actionAdd(): void
    {
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::EDIT) */
    public function actionEdit(int $eventId): void
    {
        $event = ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event [{$eventId}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_EVENTS,InstruktoriBrno\TMOU\Enums\Action::DELETE) */
    public function actionDelete(int $eventId): void
    {
        $event = ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event [{$eventId}].");
        }
        $this->template->event = $event;
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
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MottoTooLongException $e) {
                /** @var TextInput $input */
                $input = $form['motto'];
                $input->addError('Motto ročníku je příliš dlouhé, maximum je 255 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventNumberException $e) {
                /** @var TextInput $input */
                $input = $form['number'];
                $input->addError('Číslo ročníku není číslo nebo není kladné.');
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
        return $this->confirmFormFactory->create(function (Form $form, $values) {
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
}
