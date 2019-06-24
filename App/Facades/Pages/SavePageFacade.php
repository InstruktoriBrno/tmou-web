<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Pages;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Page;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use InstruktoriBrno\TMOU\Services\Pages\IsDefaultPageInEventUniqueService;
use InstruktoriBrno\TMOU\Services\Pages\IsPageSLUGInEventUniqueService;
use InstruktoriBrno\TMOU\Services\Pages\IsPageSLUGReservedService;
use Nette\Utils\ArrayHash;

class SavePageFacade
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var IsPageSLUGInEventUniqueService */
    private $isPageSLUGInEventUniqueService;

    /** @var FindEventService */
    private $findEventService;

    /** @var IsPageSLUGReservedService */
    private $isPageSLUGReservedService;

    /** @var IsDefaultPageInEventUniqueService */
    private $isDefaultPageInEventUniqueService;

    public function __construct(
        EntityManagerInterface $entityManager,
        IsPageSLUGInEventUniqueService $isPageSLUGInEventUniqueService,
        FindEventService $findEventService,
        IsPageSLUGReservedService $isPageSLUGReservedService,
        IsDefaultPageInEventUniqueService $isDefaultPageInEventUniqueService
    ) {
        $this->entityManager = $entityManager;
        $this->isPageSLUGInEventUniqueService = $isPageSLUGInEventUniqueService;
        $this->findEventService = $findEventService;
        $this->isPageSLUGReservedService = $isPageSLUGReservedService;
        $this->isDefaultPageInEventUniqueService = $isDefaultPageInEventUniqueService;
    }

    /**
     * @param ArrayHash $values
     * @param Page|null $page
     *
     * @return Page
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\SLUGTooLongException
     * @throws \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\NoSuchEventException
     * @throws \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\NonUniqueSLUGException
     * @throws \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\SLUGIsReservedException
     * @throws \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\TooManyDefaultPagesException
     */
    public function __invoke(ArrayHash $values, ?Page $page): Page
    {
        $event = null;
        if ($values->event !== null) {
            $event = ($this->findEventService)($values->event);
            if ($event === null) {
                throw new \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\NoSuchEventException();
            }
        }

        if ($page !== null) {
            $page->change(
                $values->title,
                $values->slug,
                $values->heading,
                $event,
                $values->content,
                (bool) $values->hidden,
                (bool) $values->default,
                $values->revealAt
            );
        } else {
            $page = new Page(
                $values->title,
                $values->slug,
                $values->heading,
                $event,
                $values->content,
                (bool) $values->hidden,
                (bool) $values->default,
                $values->revealAt
            );
        }

        if (!($this->isDefaultPageInEventUniqueService)($page)) {
            throw new \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\TooManyDefaultPagesException();
        }
        if (($this->isPageSLUGReservedService)($page)) {
            throw new \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\SLUGIsReservedException();
        }
        if (!($this->isPageSLUGInEventUniqueService)($page)) {
            throw new \InstruktoriBrno\TMOU\Facades\Pages\Exceptions\NonUniqueSLUGException();
        }

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }
}
