<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamByNameService;
use Nette\Security\User;

class TeamLoginFacade
{
    /** @var FindTeamByNameService */
    private $findTeamByNameService;

    /** @var User */
    private $user;

    public function __construct(
        FindTeamByNameService $deleteEventService,
        User $user
    ) {
        $this->findTeamByNameService = $deleteEventService;
        $this->user = $user;
    }

    /**
     * Takes care about complete deletion of event with given ID
     *
     * @param Event $event
     * @param string $name
     * @param string $password
     *
     * @return Team
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\InvalidTeamPasswordException
     */
    public function __invoke(Event $event, string $name, string $password): Team
    {

        $team = ($this->findTeamByNameService)($event, $name);
        if ($team === null || $team->getName() !== $name) { // check due to MySQL comparison issues
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException;
        }
        if (!$team->checkPassword($password)) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\InvalidTeamPasswordException;
        }

        $identity = $team->toIdentity();
        try {
            $this->user->login($identity);
            return $team;
        } catch (\Nette\Security\AuthenticationException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TeamLoginUnknownException('Unkown team login failure.', 0, $e);
        }
    }
}
