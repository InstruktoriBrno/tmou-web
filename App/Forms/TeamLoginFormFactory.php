<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class TeamLoginFormFactory
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

        $form->addText('name', 'Jméno')
            ->setRequired('Vyplňte, prosím, jméno vašeho týmu, případně ho zkopírujte ze seznamu zaregistrovaných týmů')
            ->addRule(Form::MAX_LENGTH, 'Jméno týmu může být maximálně 191 znaků dlouhé.', 191);
        $form->addPassword('password', 'Heslo')
            ->setRequired('Vyplňte, prosím, heslo.');

        $form->addPrimarySubmit('send', 'Přihlásit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
