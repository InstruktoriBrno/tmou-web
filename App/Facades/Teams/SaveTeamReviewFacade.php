<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Teams;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Model\TeamReview;
use InstruktoriBrno\TMOU\Services\System\GameClockService;
use Nette\Utils\ArrayHash;

class SaveTeamReviewFacade
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GameClockService */
    private $gameClockService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameClockService $gameClockService
    ) {
        $this->entityManager = $entityManager;
        $this->gameClockService = $gameClockService;
    }

    /**
     * @param ArrayHash $values
     * @param Team $team
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateTeamReviewBeforeEventEndException
     */
    public function __invoke(ArrayHash $values, Team $team): void
    {
        if (!$team->getEvent()->isPeriodForTeamReviews($this->gameClockService->get())) {
            throw new \InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateTeamReviewBeforeEventEndException;
        }
        $review = $team->getReview();
        if ($review === null) {
            $review = new TeamReview($values->positives, $values->negatives, $values->others, $values->link);
            $team->addReview($review);
        } else {
            $review->changeReview($values->positives, $values->negatives, $values->others, $values->link);
        }
        $this->entityManager->persist($team);
        $this->entityManager->flush();
    }
}
