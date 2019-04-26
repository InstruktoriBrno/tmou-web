<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Enums;

class PrivilegeEnforceMethod
{
    public const ACCESS_DENIED = 1;

    public const NOT_AVAILABLE = 2;

    /**
     * @param string|int $value
     *
     * @return int
     */
    public static function from($value) : int
    {
        if ((int) $value === static::ACCESS_DENIED) {
            return static::ACCESS_DENIED;
        }
        if ((int) $value === static::NOT_AVAILABLE) {
            return static::NOT_AVAILABLE;
        }
        throw new \InvalidArgumentException("Invalid value [$value]");
    }
}
