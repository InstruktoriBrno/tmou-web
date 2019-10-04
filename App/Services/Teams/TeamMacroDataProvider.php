<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
use InstruktoriBrno\TMOU\Model\Team;

class TeamMacroDataProvider
{
    /** @var Team */
    private $team;

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getTeamName(): string
    {
        if ($this->team === null) {
            return '';
        }
        return $this->team->getName();
    }

    public function getTeamPhrase(): string
    {
        if ($this->team === null) {
            return '';
        }
        return $this->team->getPhrase();
    }

    public function getTeamId(): string
    {
        if ($this->team === null) {
            return '';
        }
        return (string) $this->team->getId();
    }

    public function getTeamNumber(): string
    {
        if ($this->team === null) {
            return '';
        }
        return (string) $this->team->getNumber();
    }

    public function getTeamVariableSymbol(): string
    {
        if ($this->team === null) {
            return '';
        }
        $code = $this->team->getPaymentPairingCode();
        if ($code === null) {
            return 'Nebyl přidělen';
        }
        return $code;
    }

    public function getTeamStateRaw(): string
    {
        if ($this->team === null) {
            return '';
        }
        return (string) $this->team->getGameStatus()->toScalar();
    }

    public function getTeamState(): string
    {
        if ($this->team === null) {
            return '';
        }
        $state = $this->team->getGameStatus();
        if ($state->equals(GameStatus::REGISTERED())) {
            return 'registrovaný';
        }
        if ($state->equals(GameStatus::PLAYING())) {
            return 'hrající';
        }
        if ($state->equals(GameStatus::NOT_QUALIFIED())) {
            return 'nekvalifikovaný';
        }
        if ($state->equals(GameStatus::QUALIFIED())) {
            return 'kvalifikovaný';
        }
        return 'neznámý';
    }

    public function getPaymentState(): string
    {
        if ($this->team === null) {
            return '';
        }
        $state = $this->team->getPaymentStatus();
        if ($state->equals(PaymentStatus::PAID())) {
            return 'zaplaceno';
        }
        if ($state->equals(PaymentStatus::NOT_PAID())) {
            return 'nezaplaceno';
        }
        return 'neznámý';
    }
}
