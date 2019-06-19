<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Events\IsEventNumberUniqueService;
use Nette\Utils\ArrayHash;

class SaveEventFacade
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var IsEventNumberUniqueService */
    private $isEventNumberUniqueService;

    public function __construct(
        EntityManagerInterface $entityManager,
        IsEventNumberUniqueService $eventNumberUniqueService
    ) {
        $this->entityManager = $entityManager;
        $this->isEventNumberUniqueService = $eventNumberUniqueService;
    }

    /**
     * @param ArrayHash $values
     * @param Event|null $event
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MottoTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventNumberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingQualificationIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\ExcessQualificationIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventQualificationIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingQualifiedTeamCountException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamCountException
     * @throws \InstruktoriBrno\TMOU\Facades\Events\Exceptions\NonUniqueEventNumberException
     */
    public function __invoke(ArrayHash $values, ?Event $event): void
    {
        if ($event !== null) {
            $event->updateDetails(
                $values->name,
                (int) $values->number,
                $values->motto,
                (bool) $values->hasQualification,
                $values->qualificationStart,
                $values->qualificationEnd,
                $values->qualifiedTeamCount === '' ? null : (int) $values->qualifiedTeamCount,
                $values->eventStart,
                $values->eventEnd,
                $values->totalTeamCount === '' ? null : (int) $values->totalTeamCount
            );
        } else {
            $event = new Event(
                $values->name,
                (int) $values->number,
                $values->motto,
                (bool) $values->hasQualification,
                $values->qualificationStart,
                $values->qualificationEnd,
                $values->qualifiedTeamCount === '' ? null : (int) $values->qualifiedTeamCount,
                $values->eventStart,
                $values->eventEnd,
                $values->totalTeamCount === '' ? null : (int) $values->totalTeamCount
            );
        }
        if (!($this->isEventNumberUniqueService)($event)) {
            throw new \InstruktoriBrno\TMOU\Facades\Events\Exceptions\NonUniqueEventNumberException;
        }
        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }
}