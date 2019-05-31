<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

final class HomepagePresenter extends BasePresenter
{

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PUBLIC,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionDefault(): void
    {
    }
}
