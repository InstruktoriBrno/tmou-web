<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="level")
 */
class Level
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * @var Event
     */
    protected Event $event;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected int $levelNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected ?string $link;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string|null
     */
    protected ?string $backupLink;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    protected ?int $neededCorrectAnswers;

    public function __construct(
        Event $event,
        int $levelNumber,
        ?string $link,
        ?string $backupLink,
        ?int $neededCorrectAnswers
    ) {
        $this->event = $event;
        $this->levelNumber = $levelNumber;
        $this->link = $link;
        $this->backupLink = $backupLink;
        $this->neededCorrectAnswers = $neededCorrectAnswers;
    }

    public function getId(): int
    {
        if (!isset($this->id)) {
            throw new \InstruktoriBrno\TMOU\Model\Exceptions\IDNotYetAssignedException;
        }
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getLevelNumber(): int
    {
        return $this->levelNumber;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getBackupLink(): string
    {
        return $this->backupLink;
    }

    public function getNeededCorrectAnswers(): int
    {
        return $this->neededCorrectAnswers;
    }

    public function isLast(): bool
    {
        return $this->link === null || $this->neededCorrectAnswers === null;
    }
}
