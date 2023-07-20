<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamPairsFromEventService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class DeleteQualificationProgressFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    private FindTeamPairsFromEventService $findTeamPairsFromEventService;

    public function __construct(FindTeamPairsFromEventService $findTeamPairsFromEventService, FormFactory $factory)
    {
        $this->factory = $factory;
        $this->findTeamPairsFromEventService = $findTeamPairsFromEventService;
    }
    public function create(callable $onSuccess, Event $event): Form
    {
        $form = $this->factory->create();

        $form->addSelect('scope', 'Smazat průběh', ($this->findTeamPairsFromEventService)($event))
            ->setPrompt('!!! Všech týmů v ročníku !!!');

        $form->addPrimarySubmit('confirm', 'Smazat průběh')
            ->setHtmlAttribute('onClick', 'return window.confirm(\'Opravdu chcete smazat průběh? Tato akce je nevratná.\')');
        $form->onSuccess[] = static function (Form $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
