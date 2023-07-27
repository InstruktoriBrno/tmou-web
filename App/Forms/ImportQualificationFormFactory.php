<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\Html;

class ImportQualificationFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }
    public function create(callable $onSuccess, string $specificationLink, string $schemaLink): Form
    {
        $form = $this->factory->create();

        $description = Html::el();
        $description->addHtml(Html::el('br'));
        $description->addText('Ukázkovou specifikaci naleznete ');
        $description->addHtml(Html::el('a')->addAttributes(['target' => '_blank'])->href($specificationLink)->setText('zde'));
        $description->addText(', XSD schéma ');
        $description->addHtml(Html::el('a')->addAttributes(['target' => '_blank'])->href($schemaLink)->setText('zde'));
        $description->addText('.');
        $form->addUpload('specification', 'XML specifikace')
            ->setRequired('Vyberte, prosím, soubor s XML specifikací kvalifikace k importu.')
            ->addRule(Form::MIME_TYPE, 'Soubor s XML specifikací kvalifikace k importu musí být ve formátu XML.', 'text/xml')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost souboru s XML specifikací kvalifikace k importu je 1 MB.', 1024 * 1024 /* 1 MB */)
            ->setOption('description', $description);

        $form->addPrimarySubmit('import', 'Importovat');
        $form->onSuccess[] = static function (Form $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
