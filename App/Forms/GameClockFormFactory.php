<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class GameClockFormFactory
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;

    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();

        $form->addDateTimePicker('newNow', 'Nový čas');

        $form->addPrimarySubmit('send', 'Nastavit');
        $form->addSubmit('reset', 'Resetovat');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
