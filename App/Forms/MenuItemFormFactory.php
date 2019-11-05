<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsService;
use InstruktoriBrno\TMOU\Services\Pages\FindPagesPairsService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class MenuItemFormFactory
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var FindEventsPairsService */
    private $findEventsPairsService;

    /** @var FindPagesPairsService */
    private $findPagesPairsService;

    public function __construct(FindEventsPairsService $findEventsPairsService, FindPagesPairsService $findPagesPairsService, FormFactory $factory)
    {
        $this->factory = $factory;
        $this->findEventsPairsService = $findEventsPairsService;
        $this->findPagesPairsService = $findPagesPairsService;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();

        $form->addGroup('Odkaz');
        $form->addText('content', 'Text odkazu')
            ->setRequired('Zadejte, prosím, text odkazu');
        $form->addText('title', 'Tooltip')
            ->setRequired(false);
        $form->addText('class', 'CSS třídy')
            ->setRequired(false);
        $form->addText('tag', 'Tag skupiny a řazení')
            ->setRequired(false)
            ->setOption('description', 'Zadejte řetězec, který nebude vidět, ale podle něho budou odkazy rozděleny do skupin a tyto skupiny seřazeny (abecedně).');
        $form->addText('label', 'Nadpis skupiny')
            ->setRequired(false)
            ->setOption('description', 'Bude použit nadpis prvního odkazu ve skupině.');
        $form->addText('weight', 'Váha ve skupině')
            ->setRequired('Zadejte, prosím, váhu odkazu v rámci skupiny.')
            ->setOption('description', ' Těžší položky budou níže.')
            ->addRule(Form::NUMERIC, 'Váha musí být celé číslo.')
            ->setDefaultValue(0);

        $form->addGroup('Cíl');
        $type = $select = $form->addSelect('type', 'Typ', [
            'page' => 'Stránka',
            'page2' => 'Systémová stránka',
            'external' => 'URL adresa',
        ]);
        $select->addCondition($form::EQUAL, 'external')
            ->toggle('target-url');
        $select->addCondition($form::EQUAL, 'page')
            ->toggle('target-page');
        $select->addCondition($form::EQUAL, 'page2')
            ->toggle('target-event')
            ->toggle('target-slug');

        $form->addSelect('target_event', 'Ročník', ($this->findEventsPairsService)())
            ->setPrompt('Mimo ročníky')
            ->setOption('id', 'target-event')
            ->setOption('description', 'Mimo ročníky je podporována pouze stránka "Diskuze".');
        $targetPage = $form->addSelect('target_page', 'Stránka', ($this->findPagesPairsService)());
        $targetPage
            ->setOption('id', 'target-page')
            ->setPrompt('')
            ->getControlPrototype()->addAttributes(['class' => 'selectize']);
        $form->addSelect('target_slug', 'Systémová stránka', ReservedSLUG::toList())
            ->setOption('id', 'target-slug');
        $targetUrl = $form->addText('target_url', 'URL');
        $targetUrl
            ->setOption('id', 'target-url')
            ->setRequired(false)
            ->addRule(Form::URL, 'Zadaná cílová adresa není absolutní URL');

        $targetPage->addConditionOn($type, Form::EQUAL, 'page')
            ->setRequired('Vyberte cílovou stránku.');

        $targetUrl->addConditionOn($type, Form::EQUAL, 'external')
            ->setRequired('Vyplňte URL cílové stránky.');

        $form->addGroup('Zobrazení & odhalování');
        $form->addCheckbox('for_anonymous', 'Pouze pro nepřihlášené')
            ->setOption('description', 'Při zaškrnutí se objeví pouze nepřihlášeným. Další níže uvedené zaškrtávací volby už nemají žádný vliv.');
        $form->addCheckbox('for_organizators', 'Pro organizátory')
            ->setOption('description', 'Při zaškrnutí se objeví při přihlášení jako organizátor. Při zaškrnutí "Pro týmy" bude viditelné organizátorům i týmům.');
        $form->addCheckbox('for_teams', 'Pro týmy')
            ->setOption('description', 'Při zaškrnutí se objeví při přihlášení jako tým. Při zaškrnutí "Pro organizátory" bude viditelné organizátorům i týmům.');
        $form->addDateTimePicker('hide_at', 'Skrýt ve');
        $form->addDateTimePicker('reveal_at', 'Odhalit ve');


        $form->addPrimarySubmit('send', 'Uložit');
        $form->addSubmit('sendAndStay', 'Uložit a zůstat');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
