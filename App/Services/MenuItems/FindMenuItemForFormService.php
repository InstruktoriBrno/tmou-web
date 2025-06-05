<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\MenuItems;

use InstruktoriBrno\TMOU\Model\MenuItem;
use function assert;
use Doctrine\ORM\EntityManagerInterface;

class FindMenuItemForFormService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given menu item as default values for form
     *
     * @param int $id
     *
     * @return array<string, mixed>
     */
    public function __invoke(int $id): array
    {
        /** @var MenuItem|null $object */
        $object = $this->entityManager->getRepository(MenuItem::class)->find($id);
        assert($object !== null);
        return [
            'content' => $object->getContent(),
            'title' => $object->getTitle(),
            'class' => $object->getClass(),
            'tag' => $object->getTag(),
            'label' => $object->getLabel(),
            'weight' => $object->getWeight(),
            'target_page' => $object->getTargetPage() !== null ? $object->getTargetPage()->getId() : null,
            'target_event' => $object->getTargetEvent() !== null ? $object->getTargetEvent()->getId() : null,
            'target_slug' => $object->getTargetSlug(),
            'target_url' => $object->getTargetUrl(),
            'for_anonymous' => $object->isForAnonymous(),
            'for_organizators' => $object->isForOrganizators(),
            'for_teams' => $object->isForTeams(),
            'hide_at' => $object->getHideAt(),
            'reveal_at' => $object->getRevealAt(),
            'type' => $this->detectType($object),
        ];
    }

    private function detectType(MenuItem $item): string
    {
        if ($item->getTargetUrl() !== null) {
            return 'external';
        }
        if ($item->getTargetPage() !== null) {
            return 'page';
        }
        return 'page2';
    }
}
