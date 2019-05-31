<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * @method static UserRole GUEST()
 * @method static UserRole TEAM()
 * @method static UserRole ORG()
 */
final class UserRole extends Enum
{
    use AutoInstances;

    public const GUEST = 'guest';
    public const TEAM = 'team';
    public const ORG = 'org';
}
