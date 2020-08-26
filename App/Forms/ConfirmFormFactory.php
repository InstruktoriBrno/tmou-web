<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class ConfirmFormFactory
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
        $form->addPrimarySubmit('yes', 'Ano')
            ->getControlPrototype()->appendAttribute('class', 'btn-danger');
        $form->addCancel('no', 'Ne')
            ->getControlPrototype()->appendAttribute('class', 'btn-secondary');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
