<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Facades\Organizators\LoginOrganizatorViaKeycloakFacade;
use InstruktoriBrno\TMOU\Grids\OrganizatorsGrid\OrganizatorsGrid;
use InstruktoriBrno\TMOU\Grids\OrganizatorsGrid\OrganizatorsGridFactory;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorForDataGrid;
use Nette\DI\Attributes\Inject;

final class AdminPresenter extends BasePresenter
{
    #[Inject]
    public LoginOrganizatorViaKeycloakFacade $loginOrganizatorViaKeycloakFacade;

    #[Inject]
    public OrganizatorsGridFactory $organizatorsGridFactory;

    #[Inject]
    public FindOrganizatorForDataGrid $findOrganizatorForDataGrid;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_COMMON,InstruktoriBrno\TMOU\Enums\Action::VIEW,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionDefault(): void
    {
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_COMMON,InstruktoriBrno\TMOU\Enums\Action::LOGIN,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionLogin(): void
    {
        try {
            ($this->loginOrganizatorViaKeycloakFacade)();
            // Following code should be unreachable  in most cases as there is a redirect inside.
            $this->flashMessage('Během přihlašování došlo k chybě, zkuste to, prosím znovu. Pokud problém přetrvává, kontaktujte správce.', Flash::DANGER);
            $this->redirect('Homepage:');
        } catch (\InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\InvalidLoginRequestException $ex) {
            $this->flashMessage('Tato žádost o přihlášení není platná, zkuste to, prosím znovu. Pokud problém přetrvává, kontaktujte správce.', Flash::DANGER);
            $this->redirect('Homepage:');
        } catch (\InstruktoriBrno\TMOU\Facades\Organizators\Exceptions\AlreadyLoggedException $ex) {
            $this->flashMessage('Již jste přihlášeni, před pokračováním se musíte odhlásit.', Flash::WARNING);
            $this->redirect('Homepage:');
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_COMMON,InstruktoriBrno\TMOU\Enums\Action::LOGIN,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionPostLogin(): void
    {
        if ($this->user->isLoggedIn() && $this->request->getParameter('state') !== null) {
            $this->flashMessage('Byli jste úspěšně přihlášeni.', Flash::SUCCESS);
            $this->redirect('Homepage:');
        } elseif ($this->request->getParameter('not_allowed') !== null) {
            $this->flashMessage('S tímto účtem se nelze přihlásit, protože není veden mezi aktuálními organizátory.', Flash::DANGER);
            $this->redirect('Homepage:');
        } else {
            $this->flashMessage('Během přihlašování došlo k chybě, zkuste to, prosím znovu. Pokud problém přetrvává, kontaktujte správce.', Flash::DANGER);
            $this->redirect('Homepage:');
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_COMMON,InstruktoriBrno\TMOU\Enums\Action::LOGOUT,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionLogout(): void
    {
        $this->user->logout(true);
        $this->flashMessage('Byli jste odhlášeni.', Flash::SUCCESS);
        $this->redirect('Homepage:');
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::ADMIN_ORGANIZATORS,InstruktoriBrno\TMOU\Enums\Action::VIEW,InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod::TRIGGER_ADMIN_LOGIN) */
    public function actionOrganizators(): void
    {
    }

    public function createComponentOrganizatorsGrid(): OrganizatorsGrid
    {
        return $this->organizatorsGridFactory->create(($this->findOrganizatorForDataGrid)());
    }
}
