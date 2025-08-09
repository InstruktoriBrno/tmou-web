<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Qualification\FindEventsWithNewAnswersService;
use InstruktoriBrno\TMOU\Services\Qualification\UpdateEventQualificationScoreboardService;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use function is_int;

class UpdateScoreboardsFacade
{
    public const MYSQL_INT_MIN = -2147483648;
    private EntityManagerInterface $entityManager;

    private FindEventsWithNewAnswersService $findEventsWithNewAnswersService;

    private UpdateEventQualificationScoreboardService $updateEventQualificationScoreboardService;
    private Cache $cache;


    public function __construct(
        EntityManagerInterface $entityManager,
        FindEventsWithNewAnswersService $findEventsWithNewAnswersService,
        UpdateEventQualificationScoreboardService $updateEventQualificationScoreboardService,
        IStorage $cacheStorage
    ) {

        $this->entityManager = $entityManager;
        $this->findEventsWithNewAnswersService = $findEventsWithNewAnswersService;
        $this->updateEventQualificationScoreboardService = $updateEventQualificationScoreboardService;
        $this->cache = new Cache($cacheStorage, __CLASS__);
    }

    public function __invoke(?Event $forcedEvent = null): void
    {
        $key = 'latest-processed-answer-id';
        $latestProcessedAnswerId = $this->cache->load($key);
        if (!is_int($latestProcessedAnswerId)) {
            $latestProcessedAnswerId = self::MYSQL_INT_MIN;
        }
        $eventsToBeUpdated = ($this->findEventsWithNewAnswersService)($latestProcessedAnswerId);
        if ($forcedEvent !== null) {
            $found = false;
            foreach ($eventsToBeUpdated as $event) {
                if ($event->getId() === $forcedEvent->getId()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $eventsToBeUpdated[] = $forcedEvent;
            }
        }
        $latestAnswersFromEvents = [];
        foreach ($eventsToBeUpdated as $event) {
            $latestAnswersFromEvents[] = $this->entityManager->wrapInTransaction(function () use ($event): int {
                return ($this->updateEventQualificationScoreboardService)($event);
            });
        }
        if (count($latestAnswersFromEvents) > 0 && $forcedEvent === null) { // do not update cache when forced as there could be some answers not processed from other events
            $this->cache->save($key, max($latestAnswersFromEvents), [Cache::Expire => '14 days']);
        }
    }
}
