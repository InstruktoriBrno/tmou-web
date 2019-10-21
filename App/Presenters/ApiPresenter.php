<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Facades\Teams\IsSSOValid;

final class ApiPresenter extends BasePresenter
{

    /** @var IsSSOValid @inject */
    public $isSSOValid;

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PUBLIC,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionVerifySSO(string $token, ?string $jwt): void
    {
        $this->sendResponse(($this->isSSOValid)($token, $jwt));
    }
}
