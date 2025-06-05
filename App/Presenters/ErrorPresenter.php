<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use Nette\Application\Helpers;
use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\Responses\ForwardResponse;
use Nette\Http;
use Nette\SmartObject;
use Tracy\ILogger;

final class ErrorPresenter implements IPresenter
{
    use SmartObject;

    private ILogger$logger;


    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }


    public function run(Request $request): IResponse
    {
        $exception = $request->getParameter('exception');

        if ($exception instanceof \Nette\Application\BadRequestException) {
            [$module, , $sep] = Helpers::splitName($request->getPresenterName());
            return new ForwardResponse($request->setPresenterName($module . $sep . 'Error4xx'));
        }

        $this->logger->log($exception, ILogger::EXCEPTION);
        return new CallbackResponse(function (Http\IRequest $httpRequest, Http\IResponse $httpResponse): void {
            if (preg_match('#^text/html(?:;|$)#', (string) $httpResponse->getHeader('Content-Type')) === 1) {
                require __DIR__ . '/templates/Error/500.phtml';
            }
        });
    }
}
