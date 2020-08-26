<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Teams;

use InstruktoriBrno\TMOU\Model\Team;

class FindTeamReviewForFormService
{
    /**
     * Returns given team review as default values for form
     *
     * @param Team $team
     *
     * @return array<string, string|null>
     */
    public function __invoke(Team $team): array
    {
        return [
            'positives' => $team->getReview() !== null ? $team->getReview()->getPositives() : null,
            'negatives' => $team->getReview() !== null ? $team->getReview()->getNegatives() : null,
            'others' => $team->getReview() !== null ? $team->getReview()->getOthers() : null,
            'link' => $team->getReview() !== null ? $team->getReview()->getLink() : null,
        ];
    }
}
