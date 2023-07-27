<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Latte\Runtime\Html as LatteHtml;

class AbsoluteLinkFilter
{

    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function __invoke(string $value): LatteHtml
    {
        // if given value is not link starting http or https then append local address
        if (strpos($value, 'http://') !== 0 && strpos($value, 'https://') !== 0) {
            if (strpos($value, '/') !== 0) {
                $value = '/' . $value;
            }
            $value = $this->basePath . $value;
        }
        return new LatteHtml($value);
    }
}
