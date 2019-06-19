<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use Nextras\Forms\Rendering\Bs4FormRenderer;

class FormFactory
{
    public function create(): BaseForm
    {
        $form = new BaseForm();
        $form->setRenderer(new Bs4FormRenderer());
        return $form;
    }
}
