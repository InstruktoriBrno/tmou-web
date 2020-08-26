<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class NewPostFormFactory
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var FindEventsPairsService */
    private $findEventsPairsService;

    public function __construct(FormFactory $factory, FindEventsPairsService $findEventsPairsService)
    {
        $this->factory = $factory;
        $this->findEventsPairsService = $findEventsPairsService;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        $form->addTextArea('content', 'Příspěvek', 40, 10)
            ->setRequired('Vyplňte, prosím, obsah prvního příspěvku');
        $form->addPrimarySubmit('create', 'Vytvořit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
