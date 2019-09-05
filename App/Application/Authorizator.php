<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Application;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Enums\UserRole;
use Nette\InvalidStateException;
use Nette\Security\IAuthorizator;
use Nette\Security\Permission;

class Authorizator implements IAuthorizator
{

    /** @var Permission */
    private $acl;

    public function __construct()
    {
        $this->acl = new Permission();
        $this->acl->addRole(UserRole::GUEST);
        $this->acl->addRole(UserRole::TEAM, UserRole::GUEST);
        $this->acl->addRole(UserRole::ORG, UserRole::GUEST);

        $this->acl->addResource(Resource::PUBLIC);
        $this->acl->addResource(Resource::PAGES);
        $this->acl->addResource(Resource::TEAM_COMMON);
        $this->acl->addResource(Resource::ADMIN_COMMON);
        $this->acl->addResource(Resource::ADMIN_ORGANIZATORS);
        $this->acl->addResource(Resource::ADMIN_EVENTS);
        $this->acl->addResource(Resource::ADMIN_PAGES);
        $this->acl->addResource(Resource::ADMIN_TEAMS);

        // Guest
        $this->acl->deny(UserRole::GUEST);

        $this->acl->allow(UserRole::GUEST, Resource::PUBLIC, Action::VIEW);
        $this->acl->allow(UserRole::GUEST, Resource::PAGES, Action::VIEW);
        $this->acl->allow(UserRole::GUEST, Resource::ADMIN_COMMON, Action::LOGIN);
        $this->acl->allow(UserRole::GUEST, Resource::TEAM_COMMON, Action::LOGIN);
        $this->acl->allow(UserRole::GUEST, Resource::TEAM_COMMON, Action::REGISTER);
        $this->acl->allow(UserRole::GUEST, Resource::TEAM_COMMON, Action::FORGOTTEN_PASSWORD);
        $this->acl->allow(UserRole::GUEST, Resource::TEAM_COMMON, Action::RESET_PASSWORD);

        // Team
        $this->acl->allow(UserRole::TEAM, Resource::TEAM_COMMON);
        $this->acl->allow(UserRole::TEAM, Resource::ADMIN_TEAMS, Action::DEIMPERSONATE);

        // Org
        $this->acl->allow(UserRole::ORG, Resource::ADMIN_COMMON);
        $this->acl->allow(UserRole::ORG, Resource::ADMIN_ORGANIZATORS);
        $this->acl->allow(UserRole::ORG, Resource::ADMIN_EVENTS);
        $this->acl->allow(UserRole::ORG, Resource::ADMIN_PAGES);
        $this->acl->allow(UserRole::ORG, Resource::ADMIN_TEAMS);
        $this->acl->deny(UserRole::ORG, Resource::TEAM_COMMON, Action::LOGIN);
        $this->acl->deny(UserRole::ORG, Resource::TEAM_COMMON, Action::REGISTER);
        $this->acl->deny(UserRole::ORG, Resource::TEAM_COMMON, Action::FORGOTTEN_PASSWORD);
        $this->acl->deny(UserRole::ORG, Resource::TEAM_COMMON, Action::RESET_PASSWORD);
    }

    /**
     * Performs a role-based authorization.
     * @param  mixed $role
     * @param  mixed $resource
     * @param  mixed $privilege
     * @return bool
     * @throws InvalidStateException
     */
    public function isAllowed($role, $resource, $privilege): bool
    {
        return $this->acl->isAllowed($role, $resource, $privilege);
    }
}
