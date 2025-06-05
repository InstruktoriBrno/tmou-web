<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class TeamForgottenPasswordFormFactory
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

        $form->addText('email', 'E-mail')
            ->setRequired('Vyplňte, prosím, e-mail vašeho týmu. Pokud jste zapomněli i ten, kontaktujte prosím organizátory.')
            ->addRule(Form::MAX_LENGTH, 'E-mail může být maximálně 255 znaků dlouhý.', 255);

        $form->addPrimarySubmit('send', 'Požádat o nové heslo');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
