<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use Nette\Application\UI\Form;
use Nextras\Forms\Rendering\Bs4FormRenderer;

class FormFactory
{
    public function create(): Form
    {
        $form = new Form();
        $form->setRenderer(new Bs4FormRenderer());
        return $form;
    }
}
