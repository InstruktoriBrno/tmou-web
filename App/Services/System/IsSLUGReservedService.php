<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\System;

use InstruktoriBrno\TMOU\Enums\ReservedSLUG;

class IsSLUGReservedService
{
    /**
     * Checks whether given slug is reserved (as certain slugs withing events cannot be used).
     *
     * @param string $slug
     *
     * @return bool
     */
    public function __invoke(string $slug): bool
    {
        try {
            ReservedSLUG::fromScalar($slug);
            return true;
        } catch (\Grifart\Enum\MissingValueDeclarationException $e) {
            return false;
        }
    }
}
