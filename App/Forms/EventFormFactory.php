<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\SmartObject;

class EventFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }
    public function create(callable $onSuccess): Form
    {
        $form = $this->factory->create();

        $form->addGroup('Obecné');
        $form->addText('name', 'Název (Motto)')
            ->setRequired('Vyplňte, prosím, název ročníku.')
            ->addRule(Form::MAX_LENGTH, 'Název ročníku může být maximálně 255 znaků dlouhý.', 255);
        $form->addText('number', 'Číslo (ročník)')
            ->setType('number')
            ->setHtmlAttribute('step', 1)
            ->setRequired('Vyplňte, prosím, číslo ročníku.')
            ->addRule(Form::NUMERIC, 'Číslo ročníku musí být přirozené číslo vyjma 0.');
        $form->addText('sorting', 'Číslo pro řazení')
            ->setRequired(false)
            ->setHtmlAttribute('step', 0.01)
            ->setType('number')
            ->addRule(Form::FLOAT, 'Číslo ročníku pro řazení musí být buď prádzné, nebo reálné číslo.')
            ->setOption('description', 'Reálné číslo pro řazení, pokud ponecháte prázdné bude nastaveno na číslo ročníku. Použivejte maximálně setiny.');
        $form->addUpload('logo', 'Logo')
            ->setRequired(false)
            ->addRule(Form::MIME_TYPE, 'Nahrávané logo musí být ve formátu PNG.', ['image/png'])
            ->setOption('description', 'Volitelné. Dojde k uložení do cesty /storage/21/logo.png');

        $form->addGroup('Registrace');
        $form->addSelect('afterRegistrationTeamGameStatus', 'Výchozí stav po registraci', [
            GameStatus::REGISTERED()->toScalar() => 'Registrovaný',
            GameStatus::QUALIFIED()->toScalar() => 'Kvalifikovaný',
            GameStatus::NOT_QUALIFIED()->toScalar() => 'Nekvalifikovaný',
            GameStatus::PLAYING()->toScalar() => 'Hrající',
        ])
            ->setOption('description', 'Určuje jaký výchozí stav budou mít týmy ihned po registraci');
        $form->addGroup('Kvalifikace');
        $form->addCheckbox('hasQualification', 'Má kvalifikaci');
        $form->addDateTimePicker('qualificationStart', 'Začátek')
            ->setHtmlAttribute('autocomplete', 'off');
        $form->addDateTimePicker('qualificationEnd', 'Konec')
            ->setHtmlAttribute('autocomplete', 'off');
        $form->addText('qualifiedTeamCount', 'Kvalifikujících se týmů')
            ->setType('number')
            ->setHtmlAttribute('step', 1)
            ->setHtmlAttribute('min', 0);

        $form->addGroup('Hra');
        $form->addDateTimePicker('registrationDeadline', 'Deadline registrace')
            ->setOption('description', 'Lze ponechat prázdné, v takovém případě nebude registrace otevřena.')
            ->setHtmlAttribute('autocomplete', 'off');
        $form->addDateTimePicker('changeDeadline', 'Deadline změn týmů')
            ->setOption('description', 'Lze ponechat prázdné, v takovém případě budou změny týmů povoleny až do začátku hry.')
            ->setHtmlAttribute('autocomplete', 'off');
        $form->addDateTimePicker('eventStart', 'Začátek')
            ->setRequired('Vyplňte, prosím, začátek hry')
            ->setHtmlAttribute('autocomplete', 'off');
        $form->addDateTimePicker('eventEnd', 'Konec')
            ->setRequired('Vyplňte, prosím, konec hry.');
        $form->addText('totalTeamCount', 'Celkový počet týmů')
            ->setType('number')
            ->setHtmlAttribute('step', 1)
            ->setHtmlAttribute('min', 1)
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addGroup('Placení');
        $form->addText('paymentPairingCodePrefix', 'Prefix VS')
            ->setRequired(false)
            ->addRule(Form::PATTERN, 'Prefix VS musí být číslo a nesmí začínat nulami.', '^([1-9][0-9]+)$')
            ->setOption('description', 'Nesmí začínat nulami.');
        $form->addText('paymentPairingCodeSuffixLength', 'Délka sufixu VS')
            ->setOption('description', 'Na kolik míst bude formátováno číslo týmu.');
        $form->addText('amount', 'Startovné')
            ->setRequired(false)
            ->setOption('description', 'Prázdné, nebo částka v celých korunách.')
            ->addRule(Form::INTEGER, 'Startovné musí být nezáporná částka v celých korunách.')
            ->addRule(Form::MIN, 'Startovné musí být nezáporná částka v celých korunách.', 0);
        /** @var TextInput $amount */
        $amount = $form['amount'];
        $form->addDatePicker('paymentDeadline', 'Deadline platby')
            ->setRequired(false)
            ->setOption(
                'description',
                'Do tohoto termínu bude systém automaticky párovat platby, poté již budete muset provádět párování ručně. Poslední párování den po tomto termínu na datech do tohoto data včetně.'
            )
            ->addConditionOn($amount, Form::FILLED)
                ->addRule(Form::FILLED, 'Vyplňte, prosím, deadline párování plateb.');

        $form->addGroup('Sebereportované startovné');
        $form->addCheckbox('selfreportedEntryFee', 'Zapnuto')
            ->setOption(
                'description',
                'Povolí v nastavení týmu pole pro sebereportované startovné, zapne automatické nastavení týmů do stavu zaplaceno a hrající pokud částka dosáhne výše uvedeného startovné.'
            );
        $form->addPrimarySubmit('send', 'Uložit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        return $form;
    }
}
