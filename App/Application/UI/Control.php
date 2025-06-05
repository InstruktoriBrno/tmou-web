<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Application\UI;

use Nette\Application\UI\Control as NetteControl;
use Nette\Application\UI\Presenter;
use Nette\Security\User;

class Control extends NetteControl
{
    use Template;

    protected User $user;

    /**
     * @param mixed $obj
     */
    protected function attached($obj) : void
    {
        parent::attached($obj);
        if ($obj instanceof Presenter) {
            $this->user = $obj->getUser();
            $this->beforeRender();
        }
    }

    public function beforeRender(): void
    {
        // another part of control life cycle
    }

    /** @return User */
    public function getUser() : User
    {
        return $this->user;
    }

    /** @param User $user */
    public function injectUser($user) : void
    {
        $this->user = $user;
    }
}
