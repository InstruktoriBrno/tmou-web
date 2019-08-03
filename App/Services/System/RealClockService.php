<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\System;

use DateTimeImmutable;

class RealClockService
{
    /**
     * Returns real current time (real from the code evaluation)
     *
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public function get(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
