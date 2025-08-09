<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsOpenedForDiscussionService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class NewThreadFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    private FindEventsPairsOpenedForDiscussionService $findEventsPairsOpenedForDiscussionService;

    public function __construct(FormFactory $factory, FindEventsPairsOpenedForDiscussionService $findEventsPairsOpenedForDiscussionService)
    {
        $this->factory = $factory;
        $this->findEventsPairsOpenedForDiscussionService = $findEventsPairsOpenedForDiscussionService;
    }
    public function create(callable $onSuccess, bool $isOrg): Form
    {
        $form = $this->factory->create();
        $form->addText('title', 'Název')
            ->setRequired('Vyplňte, prosím, název nového diskuzního vlákna')
            ->addRule(Form::MAX_LENGTH, 'Délka názvu může být maximálně 191 znaků.', 191);
        $form->addSelect('event', 'Ročník', ($this->findEventsPairsOpenedForDiscussionService)())
            ->setPrompt('Mimo ročníky');
        $form->addTextArea('content', 'První příspěvek', 40, 10)
            ->setRequired('Vyplňte, prosím, obsah prvního příspěvku');
        if (!$isOrg) {
            $form->addText('nickname', 'Přezdívka')
                ->setOption('description', 'Volitelná. Objeví se vedle názvu týmu a bude uložena v rámci tohoto přihlášení.');
        }
        if ($isOrg) {
            $form->addDateTimePicker('revealAt', 'Odhalit ve')
                ->setOption('description', 'Volitelné. Datum a čas odhalení vlákna. Netýká se organizátorů.');
        }
        $form->addPrimarySubmit('create', 'Vytvořit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
