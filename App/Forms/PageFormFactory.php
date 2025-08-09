<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsService;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\Checkbox;
use Nette\SmartObject;
use Nette\Utils\Html;
use Nextras\FormComponents\Controls\DateTimeControl;

class PageFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    private FindEventsPairsService $findEventsPairsService;

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
            Pro vytvoření stránky aktualit na úvodní stránce použijte hodnotu "' . ReservedSLUG::UPDATES()->toScalar() . '". Dále můžete použít "' .
                ReservedSLUG::QUALIFICATION_RESULTS()->toScalar() . '", "' .
                ReservedSLUG::QUALIFICATION_ANSWERS()->toScalar() . '", "' .
                ReservedSLUG::QUALIFICATION_STATISTICS()->toScalar() . '", "' .
                ReservedSLUG::QUALIFICATION_SYSTEM()->toScalar() . '", "' .
                ReservedSLUG::GAME_STATISTICS()->toScalar() . '", "' .
                ReservedSLUG::GAME_FLOW()->toScalar() . '".');
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
        /** @var DateTimeControl $revealAtInput */
        $revealAtInput = $form['revealAt'];
        $hiddenCheckbox->addConditionOn($revealAtInput, Form::FILLED)
            ->setRequired('Pro použití volby odhalení stránky musí být zaškrnuta volba skrývání stránky.');

        $form->addGroup('Obsah');
        $form->addText('title', 'Titulek')
            ->setRequired('Vyplňte, prosím, titulek stránky.');

        $form->addText('heading', 'Nadpis')
            ->setRequired('Vyplňte, prosím, nadpis stránky.');

        $contentElementId = 'page-content';
        $insertMedia = Html::el('button')
            ->setText('Vložit média')
            ->setAttribute('class', 'btn btn-secondary btn-small file-manager')
            ->setAttribute('type', 'button')
            ->setAttribute('data-target', $contentElementId);
        $form->addTextArea('content', Html::el()->addText('Obsah')->addHtml('&nbsp;')->addHtml($insertMedia), 50, 20)
            ->setRequired('Vyplňte, prosím, obsah stránky')
            ->setHtmlId($contentElementId)
            ->setOption('description', 'Pokud vkládáte přímo HTML, ověřte, že neobsahuje XSS, takový obsah již není dále kontrolován. '
                . 'Pokud použijete hodnotu "' . ReservedSLUG::QUALIFICATION_SYSTEM()->toScalar() . '", bude obsah ignorován.');


        $form->addCheckbox('caching_safe', 'Kešovat')
            ->setOption('description', 'Zaškrtněte pouze pokud stránka nepoužívá žádné týmová ani ročníková makra (keš je sdílená mezi týmy) a
             je větší (například obsahuje-li HTML bloby nad cca 30 KB).');

        $form->addPrimarySubmit('send', 'Uložit');
        $form->addSubmit('sendAndStay', 'Uložit a zůstat');
        $form->addSubmit('sendAndShow', 'Uložit a zobrazit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
