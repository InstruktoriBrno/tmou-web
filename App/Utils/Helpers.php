<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use function constant;
use function defined;
use Nette\StaticClass;
use Nette\Utils\Strings;

class Helpers
{
    use StaticClass;

    /**
     * Returns constant value of constant name specified in given string
     *
     * @param string $value
     * @return mixed
     */
    public static function stringToConstant($value)
    {
        if (Strings::contains($value, '::') && defined($value)) {
            return constant($value);
        }
        return $value;
    }
}
