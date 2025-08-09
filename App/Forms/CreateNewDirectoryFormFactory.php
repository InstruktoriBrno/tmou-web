<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class CreateNewDirectoryFormFactory
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
        $form->getElementPrototype()->addAttributes(['class' => 'ajax']);
        $form->addText('name', 'Jméno nové složky')
            ->setRequired('Vyplňte, prosím, jméno nové složky.')
            ->addRule(Form::PATTERN_ICASE, 'Neplatné jméno složky, může být složeno z písmen, číslic, pomlčky, podtržítka. Začínat musí písmenem nebo číslem.', '^[a-z0-9](?:[a-z0-9_ -]*[a-z0-9])?$');
        $form->addPrimarySubmit('create', 'Vytvořit');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
