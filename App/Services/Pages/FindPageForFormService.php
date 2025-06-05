<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Pages;

use function assert;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Page;

class FindPageForFormService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given event as default values for form
     *
     * @param int $id
     *
     * @return array<string, mixed>
     */
    public function __invoke(int $id): array
    {
        /** @var Page|null $object */
        $object = $this->entityManager->getRepository(Page::class)->find($id);
        assert($object !== null);
        return [
            'event' => $object->getEvent() !== null ? $object->getEvent()->getId() : null,
            'slug' => $object->getSlug(),
            'default' => $object->isDefault(),
            'revealAt' => $object->getRevealAt(),
            'hidden' => $object->isHidden(),
            'title' => $object->getTitle(),
            'heading' => $object->getHeading(),
            'content' => $object->getContent(),
            'caching_safe' => $object->isCachingSafe(),
        ];
    }
}
