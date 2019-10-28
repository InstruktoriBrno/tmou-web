<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;

class UploadFileFormFactory
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
        $form->getElementPrototype()->addAttributes(['class' => 'ajax']);
        $form->addMultiUpload('files', 'Soubor')
            ->setRequired('Vyberte, prosím, soubor k nahrání.')
            ->setOption('description', 'maximální povolená velikost všech souborů dohromady je 20 MB')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální povolená velikost jednoho souboru je 20 MB', 10*1024*1024);
        $form->addCheckbox('overwrite', 'Přepsat existující')
            ->setOption('description', 'Bez zaškrtnutí tohoto pole budou existující soubory přeskočeny');
        $form->addPrimarySubmit('upload', 'Nahrát');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
