<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorByIdService;
use Nette\Security\Identity;
use Nette\Security\User;

class TransformBackFromImpersonatedIdentity
{
    private User $user;

    private FindOrganizatorByIdService $findOrganizatorByIdService;

    public function __construct(User $user, FindOrganizatorByIdService $findOrganizatorByIdService)
    {
        $this->user = $user;
        $this->findOrganizatorByIdService = $findOrganizatorByIdService;
    }

    /**
     * @param Identity $identity
     * @return Identity
     *
     * @throws \InstruktoriBrno\TMOU\Services\Teams\Exceptions\DeimpersonationException
     */
    public function __invoke(Identity $identity)
    {
        if (!$this->user->isLoggedIn()
            || !$this->user->isInRole((string) UserRole::TEAM()->toScalar())
            || !isset($identity->getData()['impersonated'], $identity->getData()['impersonatedFrom'])
            || $identity->getData()['impersonated'] !== true
        ) {
            throw new \InstruktoriBrno\TMOU\Services\Teams\Exceptions\DeimpersonationException;
        }
        $organizator = ($this->findOrganizatorByIdService)($identity->getData()['impersonatedFrom']);
        if ($organizator === null) {
            throw new \InstruktoriBrno\TMOU\Services\Teams\Exceptions\DeimpersonationException;
        }
        return $organizator->toIdentity();
    }
}
