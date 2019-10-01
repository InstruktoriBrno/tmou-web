<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams\Exceptions;

use Throwable;

class PreviewException extends \InstruktoriBrno\TMOU\Exceptions\CheckedException
{
    /** @var array */
    private $data;

    public function __construct(array $data, $code = 0, Throwable $previous = null)
    {
        parent::__construct("", $code, $previous);
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
