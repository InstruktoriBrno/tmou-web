<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Facades\Teams\IsSSOValid;
use Nette\DI\Attributes\Inject;

final class ApiPresenter extends BasePresenter
{
    #[Inject]
    public IsSSOValid $isSSOValid;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PUBLIC,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionVerifySSO(string $token, ?string $jwt): void
    {
        $this->sendResponse(($this->isSSOValid)($token, $jwt));
    }
}
