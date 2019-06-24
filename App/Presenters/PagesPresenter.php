<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Facades\Pages\DeletePageFacade;
use InstruktoriBrno\TMOU\Facades\Pages\SavePageFacade;
use InstruktoriBrno\TMOU\Forms\ConfirmFormFactory;
use InstruktoriBrno\TMOU\Forms\PageFormFactory;
use InstruktoriBrno\TMOU\Grids\PagesGrid\PagesGrid;
use InstruktoriBrno\TMOU\Grids\PagesGrid\PagesGridFactory;
use InstruktoriBrno\TMOU\Services\Events\EventMacroDataProvider;
use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;
use InstruktoriBrno\TMOU\Services\Pages\FindDefaultPageValuesForFormService;
use InstruktoriBrno\TMOU\Services\Pages\FindPageForFormService;
use InstruktoriBrno\TMOU\Services\Pages\FindPageInEventService;
use InstruktoriBrno\TMOU\Services\Pages\FindPageService;
use InstruktoriBrno\TMOU\Services\Pages\FindPagesForDataGridService;
use InstruktoriBrno\TMOU\Utils\TexyFilter;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\ArrayHash;

final class PagesPresenter extends BasePresenter
{
    /** @var PagesGridFactory @inject */
    public $pagesGridFactory;

    /** @var FindPagesForDataGridService @inject */
    public $findPagesForDataGridService;

    /** @var FindEventByNumberService @inject */
    public $findEventServiceByNumber;

    /** @var FindPageService @inject */
    public $findPageService;

    /** @var PageFormFactory @inject */
    public $pageFormFactory;

    /** @var SavePageFacade @inject */
    public $savePageFacade;

    /** @var FindPageForFormService @inject */
    public $findPageForFormService;

    /** @var FindDefaultPageValuesForFormService @inject */
    public $findDefaultPageValuesForFormService;

    /** @var ConfirmFormFactory @inject */
    public $confirmFormFactory;

    /** @var DeletePageFacade @inject */
    public $deletePageFacade;

    /** @var FindPageInEventService @inject */
    public $findPageInEventService;

    /** @var EventMacroDataProvider @inject */
    public $eventMacroDataProvider;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionDefault(?int $eventNumber): void
    {
        $event = null;
        if ($eventNumber !== null) {
            $event = ($this->findEventServiceByNumber)($eventNumber);
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionShow(?string $slug = null, ?int $eventNumber = null): void
    {
        $page = ($this->findPageInEventService)($slug, $eventNumber);
        if ($page === null) {
            throw new \Nette\Application\BadRequestException("No such page with SLUG [${slug}] within event with number [${eventNumber}].");
        }
        if (!$page->isRevealed() && !$this->user->isAllowed(Resource::ADMIN_PAGES, Action::VIEW)) {
            throw new \Nette\Application\ForbiddenRequestException("Page with SLUG [${slug}] within event with number [${eventNumber}] was not yet revealed.");
        }
        if ($page->getEvent() !== null) {
            $this->eventMacroDataProvider->setEvent($page->getEvent());
        }
        $this->template->page = $page;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::QUALIFICATION) */
    public function actionQualification(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionQualificationStatistics(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionQualificationAnswers(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionTeamsRegistered(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionTeamsQualified(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionTeamsPlaying(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::CHANGE_DETAILS) */
    public function actionTeamSettings(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionGameReports(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionGameStatistics(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionGameFlow(int $eventNumber): void
    {
        $event = ($this->findEventServiceByNumber)($eventNumber);
        if ($event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_PAGES,InstruktoriBrno\TMOU\Enums\Action::CREATE) */
    public function actionAdd(): void
    {
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_PAGES,InstruktoriBrno\TMOU\Enums\Action::EDIT) */
    public function actionEdit(int $pageId): void
    {
        $page = ($this->findPageService)($pageId);
        if ($page === null) {
            throw new \Nette\Application\BadRequestException("No such page [{$pageId}].");
        }
        $this->template->page = $page;
        $this->template->help = TexyFilter::getSyntaxHelp();
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_PAGES,InstruktoriBrno\TMOU\Enums\Action::DELETE) */
    public function actionDelete(int $pageId): void
    {
        $page = ($this->findPageService)($pageId);
        if ($page === null) {
            throw new \Nette\Application\BadRequestException("No such page [{$pageId}].");
        }
        $this->template->page = $page;
    }

    public function createComponentPagesGrid(): PagesGrid
    {
        $eventNumber = $this->getParameter('eventNumber') !== null ? (int) $this->getParameter('eventNumber') : null;
        $event = null;
        if ($eventNumber !== null) {
            $event = ($this->findEventServiceByNumber)($eventNumber);
        }
        return $this->pagesGridFactory->create(
            ($this->findPagesForDataGridService)($event)
        );
    }

    public function createComponentPageForm(): Form
    {
        $pageId = $this->getParameter('pageId') !== null ? (int) $this->getParameter('pageId') : null;
        $eventId = $this->getParameter('eventId') !== null ? (int) $this->getParameter('eventId') : null;
        $page = $pageId !== null ? ($this->findPageService)($pageId) : null;
        $form = $this->pageFormFactory->create(function (Form $form, ArrayHash $values) use ($page, $pageId): void {
            if (($pageId !== null && ! $this->user->isAllowed(Resource::ADMIN_PAGES, Action::EDIT))
                || ($pageId === null && ! $this->user->isAllowed(Resource::ADMIN_PAGES, Action::CREATE))
            ) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');

                return;
            }

            try {
                $page = ($this->savePageFacade)($values, $page);
                $this->flashMessage('Stránka byla úspěšně uložena.', Flash::SUCCESS);
                $this->redirect('Pages:', $page->getEvent() !== null ? $page->getEvent()->getNumber() : null);
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\SLUGTooLongException $e) {
                /** @var TextInput $input */
                $input = $form['slug'];
                $input->addError('SLUG stránky je příliš dlouhý, maximum je 255 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Pages\Exceptions\NonUniqueSLUGException $e) {
                /** @var TextInput $input */
                $input = $form['slug'];
                $input->addError('SLUG stránky musí být v rámci ročníku unikátní.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Pages\Exceptions\TooManyDefaultPagesException $e) {
                $form->addError('V ročníku může být maximálně jedna výchozí stránka.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Pages\Exceptions\SLUGIsReservedException $e) {
                /** @var TextInput $input */
                $input = $form['slug'];
                $input->addError('Tento SLUG stránky je rezervovaný pro systémové stránky, použijte jiný.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Pages\Exceptions\NoSuchEventException $e) {
                $form->addError('Uložení formuláře selhalo, vybraný ročník již zřejmě neexistuje.');
                return;
            }
        });
        if ($pageId !== null) {
            $values = ($this->findPageForFormService)($pageId);
        } else {
            $values = ($this->findDefaultPageValuesForFormService)($eventId);
        }
        $form->setDefaults($values);
        return $form;
    }

    public function createComponentConfirmForm(): Form
    {
        return $this->confirmFormFactory->create(function (Form $form, $values) {
            $page = ($this->findPageService)((int) $this->getParameter('pageId'));
            $eventNumber = $page !== null && $page->getEvent() !== null ? $page->getEvent()->getNumber() : null;

            /** @var SubmitButton $yes */
            $yes = $form['yes'];
            if ($yes->isSubmittedBy()) {
                if (!$this->user->isAllowed(Resource::ADMIN_PAGES, Action::DELETE)) {
                    $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                    return;
                }
                try {
                    ($this->deletePageFacade)((int) $this->getParameter('pageId'));
                } catch (\InstruktoriBrno\TMOU\Facades\Events\Exceptions\EventDeleteFailedException $exception) {
                    $form->addError('Stránku se nepodařilo smazat.');
                    return;
                }
                $this->flashMessage('Stránka byla úspěšně smazána.', Flash::SUCCESS);
                $this->redirect('Pages:', $eventNumber);
            } else {
                $this->redirect('Pages:', $eventNumber);
            }
        });
    }
}
