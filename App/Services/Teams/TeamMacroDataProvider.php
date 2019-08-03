<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

class TeamMacroDataProvider
{
    public function getTeamName(): string
    {
        return 'Fake team';
    }

    public function getTeamId(): string
    {
        return '123';
    }

    public function getTeamVariableSymbol(): string
    {
        return '11000123';
    }

    public function getTeamState(): string
    {
        return 'qualified';
    }
}
