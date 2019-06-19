<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use function count;
use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\PrivilegeEnforceMethod;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Utils\Helpers;
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\MethodReflection;
use Nette\Application\UI\Presenter;

abstract class BasePresenter extends Presenter
{

    protected function beforeRender()
    {
        parent::beforeRender();
        if ($this->user->isAllowed(Resource::ADMIN_COMMON, Action::VIEW)) {
            $this->template->hasDatagrid = true;
            $this->template->hasGlyphicons = true;
            $this->template->hasDatetimepicker = true;
        }
    }

    /** @param mixed $element */
    public function checkRequirements($element): void
    {
        if ($element instanceof MethodReflection) {
            $privilege = ComponentReflection::parseAnnotation($element, 'privilege');
            if ($privilege !== false && count($privilege) >= 2) {
                $this->requirePrivilege(
                    Helpers::stringToConstant($privilege[0]),
                    Helpers::stringToConstant($privilege[1]),
                    isset($privilege[2])
                        ? PrivilegeEnforceMethod::from(Helpers::stringToConstant($privilege[2]))
                        : PrivilegeEnforceMethod::ACCESS_DENIED
                );
            } else {
                throw new \Nette\Application\ForbiddenRequestException("Annotation @privilege of method {$element->getName()} has invalid count of parameters.");
            }
        } else {
            parent::checkRequirements($element);
        }
    }

    /**
     * @param string $resource
     * @param null $action
     * @param int $method
     *
     * @throws \Nette\Application\AbortException
     */
    public function requirePrivilege($resource, $action = null, $method = PrivilegeEnforceMethod::ACCESS_DENIED): void
    {
        if (!$this->user->isAllowed($resource, $action)) {
            if (!$this->user->isLoggedIn()) {
                $this->flashMessage('Nejprve se přihlaste.', Flash::INFO);
                $this->redirect('Admin:login', ['backlink' => $this->storeRequest()]);
            } else {
                $this->flashMessage('Nejste oprávněni k použití této funkcionality. Pokud věříte, že jde o chybu kontaktujte správce', Flash::DANGER);
                if ($method === PrivilegeEnforceMethod::NOT_AVAILABLE) {
                    $this->redirect('Homepage:', $this->user->getId());
                } else {
                    $this->forward('Error4xx:', [new \Nette\Application\ForbiddenRequestException()]);
                }
            }
        }
    }
}
