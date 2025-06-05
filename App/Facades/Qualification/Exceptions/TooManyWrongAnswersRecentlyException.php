<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Qualification\Exceptions;

use DateTimeImmutable;

class TooManyWrongAnswersRecentlyException extends \InstruktoriBrno\TMOU\Exceptions\CheckedException
{
    private DateTimeImmutable $waitUntil;

    public function __construct(DateTimeImmutable $waitUntil, string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->waitUntil = $waitUntil;
    }

    public function getWaitUntil(): DateTimeImmutable
    {
        return $this->waitUntil;
    }
}
