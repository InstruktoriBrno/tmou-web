<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * @method static LoginContinueToIntents QUALIFICATION()
 * @method static LoginContinueToIntents WEBINFO()
 */
final class LoginContinueToIntents extends Enum
{
    use AutoInstances;

    private const QUALIFICATION = 'qualification';
    private const WEBINFO = 'webinfo';
}
