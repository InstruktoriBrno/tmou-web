<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 *
 * @method static ReservedSLUG QUALIFICATION_RESULTS()
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
 * @method static ReservedSLUG UPDATES()
 *
 * @method static ReservedSLUG TEAMS_REGISTERED()
 * @method static ReservedSLUG TEAMS_QUALIFIED()
 * @method static ReservedSLUG TEAMS_PLAYING()
 *
 * @method static ReservedSLUG TEAM_REPORT()
 *
 * @method static ReservedSLUG GAME_REPORTS()
 * @method static ReservedSLUG GAME_STATISTICS()
 * @method static ReservedSLUG GAME_FLOW()
 *
 */
final class ReservedSLUG extends Enum
{
    use AutoInstances;

    public const QUALIFICATION_RESULTS = 'qualification-results'; // this can be added manually as this page is optional
    public const QUALIFICATION_STATISTICS = 'qualification-statistics'; // this can be added manually as this page is optional
    public const QUALIFICATION_ANSWERS = 'qualification-answers'; // this can be added manually as this page is optional

    public const REGISTRATION = 'registration';
    public const LOGIN = 'login';
    public const LOGOUT = 'logout';
    public const FORGOTTEN_PASSWORD = 'forgotten-password';
    public const RESET_PASSWORD = 'reset-password';
    public const SETTINGS = 'settings';

    public const UPDATES = 'updates'; // this can be added manually as this page is optional

    public const TEAMS_REGISTERED = 'teams-registered';
    public const TEAMS_QUALIFIED = 'teams-qualified';
    public const TEAMS_PLAYING = 'teams-playing';

    public const TEAM_REPORT = 'team-report';

    public const GAME_REPORTS = 'game-reports';
    public const GAME_STATISTICS = 'game-statistics'; // this can be added manually as this page is optional
    public const GAME_FLOW = 'game-flow'; // this can be added manually as this page is optional

    public function isCreationAllowed(): bool
    {
        return $this->equals(self::UPDATES())
            || $this->equals(self::QUALIFICATION_RESULTS())
            || $this->equals(self::QUALIFICATION_ANSWERS())
            || $this->equals(self::QUALIFICATION_STATISTICS())
            || $this->equals(self::GAME_STATISTICS())
            || $this->equals(self::GAME_FLOW());
    }

    public static function toList(): array
    {
        return [
            self::QUALIFICATION_RESULTS => 'Výsledky kvalifikace',
            self::QUALIFICATION_STATISTICS => 'Statistika kvalifikace',
            self::QUALIFICATION_ANSWERS => 'Odpovědi kvalifikace',
            self::REGISTRATION => 'Registrace',
            self::LOGIN => 'Přihlášení',
            self::LOGOUT => 'Odhlášení',
            self::FORGOTTEN_PASSWORD => 'Zapomenuté heslo',
            self::RESET_PASSWORD => 'Obnova hesla',
            self::SETTINGS => 'Nastavení',
            self::UPDATES => 'Aktuality',
            self::TEAMS_REGISTERED => 'Zaregistrované týmy',
            self::TEAMS_QUALIFIED => 'Kvalifikované týmy',
            self::TEAM_REPORT => 'Reportáž týmu',
            self::TEAMS_PLAYING => 'Hrající týmy',
            self::GAME_REPORTS => 'Reporty ze hry',
            self::GAME_STATISTICS => 'Statistiky hry',
            self::GAME_FLOW => 'Průběh hry',
        ];
    }
}
