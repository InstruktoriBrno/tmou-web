<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 *
 * @method static ReservedSLUG QUALIFICATION()
 * @method static ReservedSLUG QUALIFICATION_STATISTICS()
 * @method static ReservedSLUG QUALIFICATION_ANSWERS()
 *
 * @method static ReservedSLUG REGISTRATION()
 * @method static ReservedSLUG LOGIN()
 * @method static ReservedSLUG LOGOUT()
 * @method static ReservedSLUG FORGOTTEN_PASSWORD()
 * @method static ReservedSLUG RESET_PASSWORD()
 * @method static ReservedSLUG SETTINGS()
 *
 * @method static ReservedSLUG TEAMS_REGISTERED()
 * @method static ReservedSLUG TEAMS_QUALIFIED()
 * @method static ReservedSLUG TEAMS_PLAYING()
 *
 * @method static ReservedSLUG GAME_REPORTS()
 * @method static ReservedSLUG GAME_STATISTICS()
 * @method static ReservedSLUG GAME_FLOW()
 *
 */
final class ReservedSLUG extends Enum
{
    use AutoInstances;

    public const QUALIFICATION = 'qualification';
    public const QUALIFICATION_STATISTICS = 'qualification_statistics';
    public const QUALIFICATION_ANSWERS = 'qualification_answers';

    public const REGISTRATION = 'registration';
    public const LOGIN = 'login';
    public const LOGOUT = 'logout';
    public const FORGOTTEN_PASSWORD = 'forgotten-password';
    public const RESET_PASSWORD = 'reset-password';
    public const SETTINGS = 'settings';

    public const TEAMS_REGISTERED = 'teams_registered';
    public const TEAMS_QUALIFIED = 'teams_qualified';
    public const TEAMS_PLAYING = 'teams_playing';

    public const GAME_REPORTS = 'game_reports';
    public const GAME_STATISTICS = 'game_statistics';
    public const GAME_FLOW = 'game_flow';
}
