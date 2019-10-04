<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsService;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Checkbox;
use Nette\SmartObject;
use Nextras\Forms\Controls\DateTimePicker;

class PageFormFactory
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var FindEventsPairsService */
    private $findEventsPairsService;

    public function __construct(FindEventsPairsService $findEventsPairsService, FormFactory $factory)
    {
        $this->factory = $factory;
        $this->findEventsPairsService = $findEventsPairsService;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();

        $form->addGroup('Zařazení');

        $form->addSelect('event', 'Ročník', ($this->findEventsPairsService)())
            ->setPrompt('Žádný');
        $form->addText('slug', 'SLUG')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, 'SLUG stránky může být maximálně 191 znaků dlouhý.', 191)
            ->setOption('description', 'Slouží jako unikátní identifikace stránky v URL adrese v rámci ročníku. Některé hodnoty jsou rezervované. 
            Pro vytvoření stránky aktualit na úvodní stránce použijte hodnotu "' . ReservedSLUG::UPDATES()->toScalar() . '".');
        $form->addCheckbox('default', 'Výchozí')
            ->setOption('description', 'Určuje zda jde výchozí (úvodní) stránku ročníku, může být vždy maximálně jedna.');

        $form->addGroup('Zobrazování');
        $form->addCheckbox('hidden', 'Skrývat')
            ->setOption('description', 'Zaškrtnete-li toto pole, bude stránka skrytá až do níže nastaveného data, pokud jej nenastavíte, pak na vždy.')
            ->setRequired(false);
        $form->addDateTimePicker('revealAt', 'Odhalit po')
            ->setHtmlAttribute('autocomplete', 'off');

        /** @var Checkbox $hiddenCheckbox */
        $hiddenCheckbox = $form['hidden'];
        /** @var DateTimePicker $revealAtInput */
        $revealAtInput = $form['revealAt'];
        $hiddenCheckbox->addConditionOn($revealAtInput, Form::FILLED)
            ->setRequired('Pro použití volby odhalení stránky musí být zaškrnuta volba skrývání stránky.');

        $form->addGroup('Obsah');
        $form->addText('title', 'Titulek')
            ->setRequired('Vyplňte, prosím, titulek stránky.');

        $form->addText('heading', 'Nadpis')
            ->setRequired('Vyplňte, prosím, nadpis stránky.');

        $form->addTextArea('content', 'Obsah', 50, 20)
            ->setRequired('Vyplňte, prosím, obsah stránky');

        $form->addCheckbox('caching_safe', 'Kešovat')
            ->setOption('description', 'Zaškrtněte pouze pokud stránka nepoužívá žádné týmová ani ročníková makra (keš je sdílená mezi týmy) a je větší (například obsahuje-li HTML bloby nad cca 30 KB).');

        $form->addPrimarySubmit('send', 'Uložit');
        $form->addSubmit('sendAndStay', 'Uložit a zůstat');
        $form->addSubmit('sendAndShow', 'Uložit a zobrazit');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
