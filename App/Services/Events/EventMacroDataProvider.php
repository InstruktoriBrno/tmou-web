<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use DateTimeImmutable;
use InstruktoriBrno\TMOU\Model\Event;

class EventMacroDataProvider
{
    /** @var Event|null */
    private $event;

    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }
    public function getEventId(): string
    {
        return $this->event !== null ? (string) $this->event->getId() : '';
    }

    public function getEventName(): string
    {
        return $this->event !== null ? $this->event->getName() : '';
    }

    public function getEventNumber(): string
    {
        return $this->event !== null ? (string) $this->event->getNumber() : '';
    }

    public function getEventMotto(): string
    {
        return ''; // back compatibility, removed as name is used instead of motto
    }

    public function getEventTotalTeamCount(): ?string
    {
        return $this->event !== null && $this->event->getTotalTeamCount() !== null ? (string) $this->event->getTotalTeamCount() : null;
    }
    public function getEventQualifiedTeamCount(): ?string
    {
        return $this->event !== null && $this->event->getQualifiedTeamCount() !== null ? (string) $this->event->getQualifiedTeamCount() : null;
    }

    public function getEventGameStart(): ?DateTimeImmutable
    {
        return $this->event !== null ? $this->event->getEventStart() : null;
    }

    public function getEventGameEnd(): ?DateTimeImmutable
    {
        return $this->event !== null ? $this->event->getEventEnd() : null;
    }

    public function getEventQualificationStart(): ?DateTimeImmutable
    {
        return $this->event !== null ? $this->event->getQualificationStart() : null;
    }

    public function getEventQualificationEnd(): ?DateTimeImmutable
    {
        return $this->event !== null ? $this->event->getQualificationEnd() : null;
    }

    public function getRegistrationDeadline(): ?DateTimeImmutable
    {
        return $this->event !== null ? $this->event->getRegistrationDeadline() : null;
    }

    public function getChangeDeadline(): ?DateTimeImmutable
    {
        return $this->event !== null ? $this->event->getChangeDeadlineComputed() : null;
    }
}
