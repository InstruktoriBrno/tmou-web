<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams\Exceptions;

class PreviewException extends \InstruktoriBrno\TMOU\Exceptions\CheckedException
{
    /** @var array<int, array<string, mixed>> */
    private $data;

    /**
     * PreviewException constructor.
     * @param array<int, array<string, mixed>> $data
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $data, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("", $code, $previous);
        $this->data = $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
