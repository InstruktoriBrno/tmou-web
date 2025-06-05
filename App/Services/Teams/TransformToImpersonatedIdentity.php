<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Enums\UserRole;
use Nette\Security\Identity;
use Nette\Security\User;

class TransformToImpersonatedIdentity
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param Identity $identity
     * @return Identity
     *
     * @throws \InstruktoriBrno\TMOU\Services\Teams\Exceptions\ImpersonationException
     */
    public function __invoke(Identity $identity)
    {
        if (!$this->user->isLoggedIn() || !$this->user->isInRole(UserRole::ORG)) {
            throw new \InstruktoriBrno\TMOU\Services\Teams\Exceptions\ImpersonationException;
        }
        return new Identity(
            $identity->getId(),
            $identity->getRoles(),
            $identity->getData() + ['impersonated' => true, 'impersonatedFrom' => $this->user->getId()]
        );
    }
}
