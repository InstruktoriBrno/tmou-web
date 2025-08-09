<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class GameClockFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();

        $form->addDateTimePicker('newNow', 'Nový čas')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addPrimarySubmit('send', 'Nastavit');
        $form->addSubmit('reset', 'Obnovit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
