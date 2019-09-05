<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="team_review")
 */
class TeamReview
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $positives;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $negatives;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $others;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string
     */
    protected $link;

    /**
     * TeamReview constructor.
     * @param string $positives
     * @param string $negatives
     * @param string $others
     * @param string $link
     */
    public function __construct(string $positives, string $negatives, string $others, string $link)
    {
        $this->positives = $positives;
        $this->negatives = $negatives;
        $this->others = $others;
        $this->link = $link;
    }

    public function changeReview(string $positives, string $negatives, string $others, string $link): void
    {
        $this->positives = $positives;
        $this->negatives = $negatives;
        $this->others = $others;
        $this->link = $link;
    }

    public function getPositives(): string
    {
        return $this->positives;
    }

    public function getNegatives(): string
    {
        return $this->negatives;
    }

    public function getOthers(): string
    {
        return $this->others;
    }

    public function getLink(): string
    {
        return $this->link;
    }
}
