<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * @method static OrganizatorRole ORG()
 */
final class OrganizatorRole extends Enum
{
    use AutoInstances;

    private const ORG = 'ORG';

    public static function mapFromGroup(string $group): ?self
    {
        if (
            $group === '/Organizátoři TMOU' // deprecated
            || $group === '/tmou_org' // deprecated
            || $group === '/akce_tmou-web'
        ) {
            return self::ORG();
        }
        return null;
    }
}
