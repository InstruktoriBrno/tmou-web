<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\System;

use DateInterval;
use DateTimeImmutable;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use function serialize;
use function unserialize;

class GameClockService
{
    private const GAME_CLOCK_NAMESPACE = 'tmou_game_clock_namespace';
    private const INTERVAL = 'interval';

    /** @var RealClockService */
    private $realClockService;

    /** @var DateInterval|null */
    private $interval;

    /** @var SessionSection */
    private $sesionSection;

    public function __construct(Session $session, RealClockService $realClockService)
    {
        $this->realClockService = $realClockService;
        $this->sesionSection = $session->getSection(static::GAME_CLOCK_NAMESPACE);

        if ($this->sesionSection->offsetExists(static::INTERVAL)) {
            $this->interval = unserialize($this->sesionSection->offsetGet(static::INTERVAL), ['allowed_classes' => [DateInterval::class]]);
        }
    }

    /**
     * Returns game time which can be modified by given offset by administrators (used for simulations)
     *
     * @return DateTimeImmutable
     *
     * @throws \Exception
     */
    public function get(): DateTimeImmutable
    {
        $current = $this->realClockService->get();
        if ($this->interval !== null) {
            return $current->add($this->interval);
        }
        return $current;
    }

    /**
     * Returns true if game time is overridden, false otherwise
     * @return bool
     */
    public function isOverridden(): bool
    {
        return $this->interval !== null;
    }

    /**
     * Sets game time from given datetime which should be considered as new now.
     *
     * @param DateTimeImmutable $nowCurrent
     *
     * @throws \Exception
     */
    public function set(DateTimeImmutable $nowCurrent): void
    {
        $this->interval = $this->realClockService->get()->diff($nowCurrent);
        $this->sesionSection->offsetSet(static::INTERVAL, serialize($this->interval));
    }

    /**
     * Reset game time to the real time
     */
    public function reset(): void
    {
        $this->interval = null;
        $this->sesionSection->offsetUnset(static::INTERVAL);
    }
}
