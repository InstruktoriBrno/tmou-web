<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Services\Events\FindLatestEventService;

final class HomepagePresenter extends BasePresenter
{

    /** @var FindLatestEventService @inject */
    public $findLatestEventService;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PUBLIC,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionDefault(): void
    {
        $event = ($this->findLatestEventService)();
        if ($event === null) {
            throw new \Nette\Application\BadRequestException('No latest event, cannot properly redirect.');
        }
        $this->forward('Pages:show', null, $event->getNumber());
    }
}
