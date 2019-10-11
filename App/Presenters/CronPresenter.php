<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use DateTimeImmutable;
use InstruktoriBrno\TMOU\Facades\Events\MatchPaymentsFacade;
use Nette\Application\Responses\TextResponse;

final class CronPresenter extends BasePresenter
{
    /** @var string */
    private $apiKey;

    /** @var MatchPaymentsFacade @inject */
    public $matchPaymentsFacade;

    public function setApiKey(string $key): void
    {
        $this->apiKey = $key;
    }

    public function startup()
    {
        parent::startup();
        $key = $this->getParameter('apiKey');
        if ($key === null || $key === '' || $key !== $this->apiKey) {
            $this->getHttpResponse()->setCode(403);
            $this->sendResponse(new TextResponse('apiKey is invalid'));
            $this->terminate();
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PUBLIC,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionPayments(string $start, string $end): void
    {
        $path = __DIR__ . '/../../payments/LAST_RUN';
        $lastModified = @filemtime($path);
        if ($lastModified > time() - 30) {
            $this->sendResponse(new TextResponse('It seems that the previous ran was within previous 30 seconds. Please wait and try again as FIO API has rate limitations.'));
            $this->terminate();
        }
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $start);
        if (!$start instanceof DateTimeImmutable || (DateTimeImmutable::getLastErrors()['warning_count'] + DateTimeImmutable::getLastErrors()['error_count']) > 0) {
            $this->sendResponse(new TextResponse('start expected in format Y-m-d, i.e. 2019-12-28'));
            $this->terminate();
        }
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $end);
        if (!$end instanceof DateTimeImmutable || (DateTimeImmutable::getLastErrors()['warning_count'] + DateTimeImmutable::getLastErrors()['error_count']) > 0) {
            $this->sendResponse(new TextResponse('end expected in format Y-m-d, i.e. 2019-12-28'));
            $this->terminate();
        }
        if ($end < $start) {
            $this->sendResponse(new TextResponse('end expected to be higher or equal as start'));
            $this->terminate();
        }

        ($this->matchPaymentsFacade)($start, $end);

        $this->terminate();
    }
}
