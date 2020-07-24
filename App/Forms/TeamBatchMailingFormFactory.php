<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\PaymentStatus;
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

    /**
     * @param callable $onSuccess
     * @param Event $event
     * @param array<string>|null $filterStates
     * @param array<string>|null $filterPaymentStates
     * @return Form
     */
    public function create(callable $onSuccess, Event $event, ?array $filterStates, ?array $filterPaymentStates): Form
    {
        $form = $this->factory->create();

        $gameStates = null;
        if (is_array($filterStates)) {
            $gameStates = array_map(function (string $state): GameStatus {
                return GameStatus::fromScalar($state);
            }, $filterStates);
        }
        $paymentStates = null;
        if (is_array($filterPaymentStates)) {
            $paymentStates = array_map(function (string $state): PaymentStatus {
                return PaymentStatus::fromScalar($state);
            }, $filterPaymentStates);
        }

        $form->addMultiSelect('states', 'Odeslat týmům ve stavu', [
            null => 'Všechny',
            GameStatus::REGISTERED()->toScalar() => 'Registrovaní',
            GameStatus::QUALIFIED()->toScalar() => 'Kvalifikovaní',
            GameStatus::NOT_QUALIFIED()->toScalar() => 'Nekvalifikovaní',
            GameStatus::PLAYING()->toScalar() => 'Hrající',
        ], 5)
            ->setOption('description', 'Všem týmům mající vybrané stavy bude e-mail odeslán nehledě na další výběry týmů.');
        $form->addMultiSelect('filterStates', 'Filtrování zobrazených týmů dle stavu', [
            GameStatus::REGISTERED()->toScalar() => 'Registrovaní',
            GameStatus::QUALIFIED()->toScalar() => 'Kvalifikovaní',
            GameStatus::NOT_QUALIFIED()->toScalar() => 'Nekvalifikovaní',
            GameStatus::PLAYING()->toScalar() => 'Hrající',
        ], 5);
        $form->addMultiSelect('filterPaymentStates', 'Filtrování zobrazených týmů dle platby', [
            PaymentStatus::NOT_PAID()->toScalar() => 'Nezaplaceno',
            PaymentStatus::PAID()->toScalar() => 'Zaplaceno',
        ], 2);
        $form->addSubmit('filter', 'Filtrovat')
            ->setValidationScope(false);
        $form->addMultiSelect('teams', 'Týmy', $this->getTeams($event, $gameStates, $paymentStates), 20);
        $form->addText('subject', 'Předmět')
            ->setRequired('Vyplňte, prosím, předmět e-mailu.');
        $form->addTextArea('content', 'Obsah', 50, 20)
            ->setRequired('Vyplňte, prosím, obsah e-mailu.');

        $form->addText('skip', 'Přeskočit prvních')
            ->setType('number')
            ->setRequired(false)
            ->addRule(Form::MIN, 'Počet přeskočených adresátů musí být nezáporný.', 0)
            ->setOption('description', 'Říká kolik prvních adresátů z dávky bude přeskočeno. Použijte v případě předchozího selhání.');

        $form->addPrimarySubmit('send', 'Rozeslat')
            ->setHtmlAttribute('onClick', 'return confirm("Opravdu chcete nyní rozeslat hromadný e-mail?")');
        $form->addSubmit('preview', 'Náhled');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }

    /**
     * @param Event $event
     * @param GameStatus[]|null $gameStates
     * @param PaymentStatus[]|null $paymentStates
     * @return array<int, string>
     */
    private function getTeams(Event $event, ?array $gameStates, ?array $paymentStates): array
    {
        $teams = ($this->findTeamsPairsInEventService)($event, $gameStates, $paymentStates);
        uasort($teams, function (string $team1, string $team2): int {
            return $team1 <=> $team2;
        });
        return $teams;
    }
}
