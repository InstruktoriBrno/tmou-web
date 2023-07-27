<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Nette\Utils\Strings;

class AsciiLinkFilter
{
    public function __invoke(string $value): string
    {
        return Strings::toAscii($value);
    }
}
