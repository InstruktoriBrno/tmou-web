<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Services\Events\FindEventsPairsService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class CopyEventContentFormFactory
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

        $form->addSelect('from', 'Zdroj', ($this->findEventsPairsService)())
            ->setRequired('Vyberte, prosím, zdrojový ročník ze kterého se má kopírovat.');
        $form->addSelect('to', 'Cíl', ($this->findEventsPairsService)())
            ->setRequired('Vyberte, prosím, cílový ročník do kterého se má kopírovat.');

        $form->addPrimarySubmit('copy', 'Kopírovat');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
