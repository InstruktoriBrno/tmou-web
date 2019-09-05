<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

use Grifart\Enum\AutoInstances;
use Grifart\Enum\Enum;

/**
 * @method static PaymentStatus NOT_PAID()
 * @method static PaymentStatus PAID()
 */
final class PaymentStatus extends Enum
{
    use AutoInstances;

    private const NOT_PAID = 'NOT_PAID';
    private const PAID = 'PAID';
}
