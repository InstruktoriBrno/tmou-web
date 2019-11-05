<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Facades\MenuItems\SaveMenuItemFacade;
use InstruktoriBrno\TMOU\Facades\MenuItems\DeleteMenuItemFacade;
use InstruktoriBrno\TMOU\Forms\ConfirmFormFactory;
use InstruktoriBrno\TMOU\Forms\MenuItemFormFactory;
use InstruktoriBrno\TMOU\Grids\MenuItemsGrid\MenuItemsGrid;
use InstruktoriBrno\TMOU\Grids\MenuItemsGrid\MenuItemsGridFactory;
use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;
use InstruktoriBrno\TMOU\Services\MenuItems\FindMenuItemService;
use InstruktoriBrno\TMOU\Services\MenuItems\FindMenuItemsForDataGridService;
use InstruktoriBrno\TMOU\Services\MenuItems\FindMenuItemForFormService;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextInput;
use Nette\Utils\ArrayHash;

final class MenuPresenter extends BasePresenter
{

    /** @var FindEventByNumberService @inject */
    public $findEventServiceByNumber;

    /** @var MenuItemsGridFactory @inject */
    public $menuItemsGridFactory;

    /** @var FindMenuItemsForDataGridService @inject */
    public $findMenuItemsForDataGridService;

    /** @var MenuItemFormFactory @inject */
    public $menuItemFormFactory;

    /** @var FindMenuItemService @inject */
    public $findMenuItemService;

    /** @var ConfirmFormFactory @inject */
    public $confirmFormFactory;

    /** @var SaveMenuItemFacade @inject */
    public $saveMenuItemFacade;

    /** @var FindMenuItemForFormService @inject */
    public $findMenuItemForFormService;

    /** @var DeleteMenuItemFacade @inject */
    public $deleteMenuitemFacade;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_MENU_ITEMS,InstruktoriBrno\TMOU\Enums\Action::VIEW,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDefault(?int $eventNumber): void
    {
        $event = null;
        if ($eventNumber !== null) {
            $event = ($this->findEventServiceByNumber)($eventNumber);
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_MENU_ITEMS,InstruktoriBrno\TMOU\Enums\Action::CREATE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionAdd(?int $eventNumber): void
    {
        $event = null;
        if ($eventNumber !== null) {
            $event = ($this->findEventServiceByNumber)($eventNumber);
        }
        $this->template->event = $event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_MENU_ITEMS,InstruktoriBrno\TMOU\Enums\Action::EDIT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionEdit(int $menuItemId, ?int $eventNumber): void
    {
        $menuItem = ($this->findMenuItemService)($menuItemId);
        if ($menuItem === null) {
            throw new \Nette\Application\BadRequestException("No such menu item [{$menuItemId}].");
        }
        $this->template->menuItem = $menuItem;
        $this->template->event = $menuItem->getEvent();
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_MENU_ITEMS,InstruktoriBrno\TMOU\Enums\Action::DELETE,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDelete(int $menuItemId): void
    {
        $menuItem = ($this->findMenuItemService)($menuItemId);
        if ($menuItem === null) {
            throw new \Nette\Application\BadRequestException("No such menu item [{$menuItemId}].");
        }
        $this->template->menuItem = $menuItem;
        $this->template->event = $menuItem->getEvent();
    }

    public function createComponentMenuItemsGrid(): MenuItemsGrid
    {
        $eventNumber = $this->getParameter('eventNumber') !== null ? (int)$this->getParameter('eventNumber') : null;
        $event = null;
        if ($eventNumber !== null) {
            $event = ($this->findEventServiceByNumber)($eventNumber);
        }
        return $this->menuItemsGridFactory->create(
            ($this->findMenuItemsForDataGridService)($event),
            $eventNumber
        );
    }

    public function createComponentMenuItemForm(): Form
    {
        $menuItemId = $this->getParameter('menuItemId') !== null ? (int) $this->getParameter('menuItemId') : null;
        $eventNumber = $this->getParameter('eventNumber') !== null ? (int) $this->getParameter('eventNumber') : null;
        $event = $eventNumber !== null ? ($this->findEventServiceByNumber)($eventNumber) : null;
        if ($eventNumber !== null && $event === null) {
            throw new \Nette\Application\BadRequestException;
        }
        $menuItem = $menuItemId !== null ? ($this->findMenuItemService)($menuItemId) : null;
        $form = $this->menuItemFormFactory->create(function (Form $form, ArrayHash $values) use ($event, $menuItem, $menuItemId): void {
            if (($menuItemId !== null && ! $this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::EDIT))
                || ($menuItemId === null && ! $this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::CREATE))
            ) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }

            try {
                $menuItem = ($this->saveMenuItemFacade)($values, $menuItem, $event);
                $this->flashMessage('Položka menu byla úspěšně uložena.', Flash::SUCCESS);
                /** @var SubmitButton $sendAndStay */
                $sendAndStay = $form['sendAndStay'];
                if ($sendAndStay->isSubmittedBy()) {
                    $this->redirect('Menu:edit', $menuItem->getId(), $menuItem->getEvent() !== null ? $menuItem->getEvent()->getNumber() : null);
                }
                $this->redirect('Menu:', $menuItem->getEvent() !== null ? $menuItem->getEvent()->getNumber() : null);
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\MixedLinkOptionsException | \InstruktoriBrno\TMOU\Model\Exceptions\InvalidLinkOptionsException $e) {
                $form->addError('Nastavení cíle není validní, překontrolujte, zda je vše vyplněno.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidLinkWithoutEventException $e) {
                $form->addError('Tuto systémovou stránku nelze odkazovat bez ročníku.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidUrlException $e) {
                /** @var TextInput $input */
                $input = $form['target_url'];
                $input->addError('Cílová adresa není validní absolutní URL.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\NoSuchPageException $e) {
                $form->addError('Vybraná cílová stránka neexistuje.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\NoSuchEventException $e) {
                $form->addError('Vybraný cílový ročník neexistuje.');
                return;
            }
        });
        if ($menuItemId !== null) {
            $values = ($this->findMenuItemForFormService)($menuItemId);
        } else {
            $values = [];
        }
        $form->setDefaults($values);
        return $form;
    }

    public function createComponentConfirmForm(): Form
    {
        return $this->confirmFormFactory->create(function (Form $form, $values) {
            $menuItem = ($this->findMenuItemService)((int) $this->getParameter('menuItemId'));
            $eventNumber = $menuItem !== null && $menuItem->getEvent() !== null ? $menuItem->getEvent()->getNumber() : null;

            /** @var SubmitButton $yes */
            $yes = $form['yes'];
            if ($yes->isSubmittedBy()) {
                if (!$this->user->isAllowed(Resource::ADMIN_MENU_ITEMS, Action::DELETE)) {
                    $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                    return;
                }
                try {
                    ($this->deleteMenuitemFacade)((int) $this->getParameter('menuItemId'));
                } catch (\InstruktoriBrno\TMOU\Facades\MenuItems\Exceptions\MenuItemDeleteFailedException $exception) {
                    $form->addError('Položku menu se nepodařilo smazat.');
                    return;
                }
                $this->flashMessage('Položka menu byla úspěšně smazána.', Flash::SUCCESS);
                $this->redirect('Menu:', $eventNumber);
            } else {
                $this->redirect('Menu:', $eventNumber);
            }
        });
    }
}
