<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use DateTimeImmutable;
use InstruktoriBrno\TMOU\Model\Event;

class EventMacroDataProvider
{
    private ?Event $event;

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
        return isset($this->event) ? (string) $this->event->getId() : '';
    }

    public function getEventName(): string
    {
        return isset($this->event) ? $this->event->getName() : '';
    }

    public function getEventNumber(): string
    {
        return isset($this->event) ? (string) $this->event->getNumber() : '';
    }

    public function getEventMotto(): string
    {
        return ''; // back compatibility, removed as name is used instead of motto
    }

    public function getEventTotalTeamCount(): ?string
    {
        return isset($this->event) && $this->event->getTotalTeamCount() !== null ? (string) $this->event->getTotalTeamCount() : null;
    }
    public function getEventQualifiedTeamCount(): ?string
    {
        return isset($this->event) && $this->event->getQualifiedTeamCount() !== null ? (string) $this->event->getQualifiedTeamCount() : null;
    }

    public function getEventGameStart(): ?DateTimeImmutable
    {
        return isset($this->event) ? $this->event->getEventStart() : null;
    }

    public function getEventGameEnd(): ?DateTimeImmutable
    {
        return isset($this->event) ? $this->event->getEventEnd() : null;
    }

    public function getEventQualificationStart(): ?DateTimeImmutable
    {
        return isset($this->event) ? $this->event->getQualificationStart() : null;
    }

    public function getEventQualificationEnd(): ?DateTimeImmutable
    {
        return isset($this->event) ? $this->event->getQualificationEnd() : null;
    }

    public function getRegistrationDeadline(): ?DateTimeImmutable
    {
        return isset($this->event) ? $this->event->getRegistrationDeadline() : null;
    }

    public function getChangeDeadline(): ?DateTimeImmutable
    {
        return isset($this->event) ? $this->event->getChangeDeadlineComputed() : null;
    }
}
