<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class TeamReviewFormFactory
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

        $form->addTextArea('positives', 'Co se líbilo', 50, 10);
        $form->addTextArea('negatives', 'Co se nelíbilo', 50, 10);
        $form->addTextArea('others', 'Další dojmy a postřehy', 50, 10);
        $form->addText('link', 'Odkaz na reportáž')
            ->setRequired(false)
            ->addCondition(Form::FILLED)
            ->addRule(Form::URL, 'Odkaz na reportáž musí být absolutní adresou ve formátu https://www.example.com/stranka?parameter=hodnota...');

        $form->addPrimarySubmit('send', 'Uložit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
