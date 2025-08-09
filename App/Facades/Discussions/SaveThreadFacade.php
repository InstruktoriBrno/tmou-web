<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Discussions;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadService;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use Nette\Utils\ArrayHash;

class SaveThreadFacade
{
    private EntityManagerInterface $entityManager;

    private FindEventService $findEventService;

    private FindThreadService $findThreadService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindEventService $findEventService,
        FindThreadService $findThreadService
    ) {
        $this->entityManager = $entityManager;
        $this->findEventService = $findEventService;
        $this->findThreadService = $findThreadService;
    }

    /**
     * @param ArrayHash $values
     * @param int $threadId
     *
     * @return Thread
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\TitleIsTooLongException
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchEventException
     * @throws \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchThreadException
     *
     */
    public function __invoke(ArrayHash $values, int $threadId): Thread
    {
        $event = null;
        if ($values->event !== null) {
            $event = ($this->findEventService)($values->event);
            if ($event === null) {
                throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchEventException;
            }
        }
        if ($event !== null && $event->getEventEnd() !== null && $event->getEventEnd() < new DateTimeImmutable('-6 months')) {
            throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException;
        }

        $thread = ($this->findThreadService)($threadId);
        if ($thread === null) {
            throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchThreadException;
        }

        try {
            $thread->changeRevealAt($values->revealAt);
            $thread->changeTitle($values->title);
            $thread->changeEvent($event);
        } catch (\InstruktoriBrno\TMOU\Model\Exceptions\TitlelTooLongException $exception) {
            throw new \InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\TitleIsTooLongException;
        }

        $this->entityManager->persist($thread);
        $this->entityManager->flush();

        return $thread;
    }
}
