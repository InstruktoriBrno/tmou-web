<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsOpenedForDiscussionService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class NewThreadFormFactory
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var FindEventsPairsOpenedForDiscussionService */
    private $findEventsPairsOpenedForDiscussionService;

    public function __construct(FormFactory $factory, FindEventsPairsOpenedForDiscussionService $findEventsPairsOpenedForDiscussionService)
    {
        $this->factory = $factory;
        $this->findEventsPairsOpenedForDiscussionService = $findEventsPairsOpenedForDiscussionService;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        $form->addText('title', 'Název')
            ->setRequired('Vyplňte, prosím, název nového diskuzního vlákna')
            ->addRule(Form::MAX_LENGTH, 'Délka názvu může být maximálně 191 znaků.', 191);
        $form->addSelect('event', 'Ročník', ($this->findEventsPairsOpenedForDiscussionService)())
            ->setPrompt('Mimo ročníky');
        $form->addTextArea('content', 'První příspěvek', 40, 10)
            ->setRequired('Vyplňte, prosím, obsah prvního příspěvku');
        $form->addPrimarySubmit('create', 'Vytvořit');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}