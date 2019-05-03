<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * @method Resource PUBLIC()
 *
 * @method Resource ADMIN_PUBLIC()
 * @method Resource ADMIN_COMMON()
 * @method Resource ADMIN_ORGANIZATORS()
 *
 */
final class Resource extends Enum
{
    use AutoInstances;

    public const PUBLIC = 'public';

    public const ADMIN_COMMON = 'admin_common';
    public const ADMIN_ORGANIZATORS = 'admin_organizators';
    public const ADMIN_EVENTS = 'admin_events';

    public const TEAM_COMMON = 'team_common';
}
