<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Services\Qualification\FindPuzzlesOfEventPairsService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class QualificationAnswerFormFactory
{
    use SmartObject;

    private FormFactory $factory;
    private FindPuzzlesOfEventPairsService $findPuzzlesOfEventPairsService;


    public function __construct(FindPuzzlesOfEventPairsService $findPuzzlesOfEventPairsService, FormFactory $factory)
    {
        $this->factory = $factory;
        $this->findPuzzlesOfEventPairsService = $findPuzzlesOfEventPairsService;
    }
    public function create(callable $onSuccess, Event $event, Team $team): Form
    {
        $form = $this->factory->create();

        $form->addSelect('puzzle', 'Šifra', ($this->findPuzzlesOfEventPairsService)($event, $team))
            ->setRequired('Vyberte, prosím, šifru na kterou chcete odpovídat.');
        $form->addText('answer', 'Odpověď')
            ->setRequired('Zadejte, prosím, vaši odpověď.')
            ->setOption('description', 'Zadávejte odpověď velkými písmeny, bez diakritiky a bez počátečních a koncových mezer.')
            ->setHtmlAttribute('autofocus');

        $form->addPrimarySubmit('submit', 'Odevzdat');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
