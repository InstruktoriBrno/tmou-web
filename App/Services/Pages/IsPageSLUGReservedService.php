<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Model\Page;

class IsPageSLUGReservedService
{
    /**
     * Checks whether given changed object can be saved with its slug (as certain slugs withing events cannot be used).
     *
     * @param Page $page
     *
     * @return bool
     */
    public function __invoke(Page $page): bool
    {
        if ($page->getEvent() === null) {
            return false;
        }

        try {
            ReservedSLUG::fromScalar($page->getSlug());
            return true;
        } catch (\Grifart\Enum\MissingValueDeclarationException $e) {
            return false;
        }
    }
}
