<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use function constant;
use DateTimeImmutable;
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

    /**
     * Attempts to parse given date time string in given format and timezone.
     * All error states are checked and therefore only DateTimeImmutable without any errors and warnings are returned.
     *
     * @param string $format
     * @param string $time
     * @param \DateTimeZone|null $timeZone
     *
     * @return DateTimeImmutable
     * @throws \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException
     */
    public static function createDateTimeImmutableFromFormat(string $format, string $time, \DateTimeZone $timeZone = null): DateTimeImmutable
    {
        $output = DateTimeImmutable::createFromFormat($format, $time, $timeZone);
        $errors = DateTimeImmutable::getLastErrors();

        if ($output === false || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
            throw new \InstruktoriBrno\TMOU\Exceptions\InvalidDateTimeFormatException("Date ${time} doesn't match ${format}.");
        }
        return $output;
    }
}
