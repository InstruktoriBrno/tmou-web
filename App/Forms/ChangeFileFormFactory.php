<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Services\Files\FindStorageDirectoriesPairsService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class ChangeFileFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    private FindStorageDirectoriesPairsService $findStorageDirectoriesPairsService;

    public function __construct(FormFactory $factory, FindStorageDirectoriesPairsService $findStorageDirectoriesPairsService)
    {
        $this->factory = $factory;
        $this->findStorageDirectoriesPairsService = $findStorageDirectoriesPairsService;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();
        $form->getElementPrototype()->addAttributes(['class' => 'ajax']);
        $form->addText('original', 'Původní jméno souboru', 50)
            ->setRequired('Původní jméno souboru musí být vyplněno. Vyberte ze seznamu, který soubor chcete přejmenovat či přesunout')
            ->setHtmlAttribute('readonly', 'readonly');
        $form->addText('name', 'Nové jméno souboru', 50)
            ->setRequired('Vyplňte, prosím, nové jméno souboru')
            ->addRule(
                Form::PATTERN_ICASE,
                'Neplatné jméno souboru, může být složeno z písmen, číslic, pomlčky, podtržítka, tečky. Začínat musí písmenem nebo číslem.',
                '^[a-z0-9\.\s](?:[a-z0-9_ -\.\s]*[a-z0-9\.\s])?$'
            );
        $form->addSelect('targetDir', 'Nová rodičovská složka', ($this->findStorageDirectoriesPairsService)());
        $form->addPrimarySubmit('perform', 'Provést');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
