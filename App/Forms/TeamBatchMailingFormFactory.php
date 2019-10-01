<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsPairsInEventService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class TeamBatchMailingFormFactory
{
    use SmartObject;

    /** @var FormFactory */
    private $factory;

    /** @var FindTeamsPairsInEventService */
    private $findTeamsPairsInEventService;

    public function __construct(FormFactory $factory, FindTeamsPairsInEventService $findTeamsPairsInEventService)
    {
        $this->factory = $factory;
        $this->findTeamsPairsInEventService = $findTeamsPairsInEventService;
    }
    public function create(callable $onSuccess, Event $event): Form
    {
        $form = $this->factory->create();

        $form->addMultiSelect('states', 'Stavy', [
            null => 'Všechny',
            GameStatus::REGISTERED()->toScalar() => 'Registrovaní',
            GameStatus::QUALIFIED()->toScalar() => 'Kvalifikovaní',
            GameStatus::NOT_QUALIFIED()->toScalar() => 'Nekvalifikovaní',
            GameStatus::PLAYING()->toScalar() => 'Hrající',
        ], 5);
        $form->addMultiSelect('teams', 'Týmy', ($this->findTeamsPairsInEventService)($event), 20);
        $form->addTextArea('content', 'Obsah', 50, 20)
            ->setRequired('Vyplňte, prosím, obsah stránky');

        $form->addPrimarySubmit('send', 'Rozeslat');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
