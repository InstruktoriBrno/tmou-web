<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsPairsInEventService;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class TeamBatchGameStatusChangeFormFactory
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

        $form->addUpload('batch', 'CSV')
            ->setRequired('Musíte vybrat nahraný soubor')
            ->addRule(Form::MIME_TYPE, 'Nahraný soubor musí být typu CSV.', ['text/csv', 'text/plain']);
        $form->addSelect('other_status', 'Stav neobsažených', [
            null => 'Beze změny',
            GameStatus::REGISTERED()->toScalar() => 'Registrovaní',
            GameStatus::QUALIFIED()->toScalar() => 'Kvalifikovaní',
            GameStatus::NOT_QUALIFIED()->toScalar() => 'Nekvalifikovaní',
            GameStatus::PLAYING()->toScalar() => 'Hrající',
        ])
            ->setOption('description', 'Tento stav bude nastaven týmům, které nejsou v souboru přítomny.');

        $form->addPrimarySubmit('send', 'Provést');
        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
