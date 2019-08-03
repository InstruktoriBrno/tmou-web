<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * @method Resource PUBLIC()
 * @method Resource PAGES()
 *
 * @method Resource ADMIN_PUBLIC()
 * @method Resource ADMIN_COMMON()
 * @method Resource ADMIN_EVENTS();
 * @method Resource ADMIN_PAGES();
 * @method Resource ADMIN_ORGANIZATORS()
 *
 */
final class Resource extends Enum
{
    use AutoInstances;

    public const PUBLIC = 'public';
    public const PAGES = 'pages';

    public const ADMIN_COMMON = 'admin_common';
    public const ADMIN_ORGANIZATORS = 'admin_organizators';
    public const ADMIN_EVENTS = 'admin_events';
    public const ADMIN_PAGES = 'admin_pages';

    public const TEAM_COMMON = 'team_common';
}
