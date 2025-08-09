<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Forms;

use InstruktoriBrno\TMOU\Application\UI\BaseForm;
use Nette\Application\UI\Form;
use Nette\SmartObject;

class TeamRegistrationFormFactory
{
    use SmartObject;

    private FormFactory $factory;

    public function __construct(FormFactory $factory)
    {
        $this->factory = $factory;
    }
    public function create(callable $onSuccess, bool $registration, bool $isAfterDeadline, bool $showSelfreportedFee = false): Form
    {
        $form = $this->factory->create();

        $form->addGroup('Tým');
        $name = $form->addText('name', 'Jméno')
            ->setRequired('Vyplňte, prosím, jméno vašeho týmu.')
            ->addRule(Form::MAX_LENGTH, 'Jméno týmu může být maximálně 191 znaků dlouhé.', 191)
            ->setOption('description', 'Jméno týmu budete používat k přihlašování.');
        $phrase = $form->addText('phrase', 'Tajná fráze')
            ->setRequired('Vyplňte, prosím, tajnou frázi vašeho týmu.')
            ->addRule(Form::MAX_LENGTH, 'Tajná fráze může být maximálně 255 znaků dlouhá.', 255)
            ->setOption('description', 'Někdy používáno ke k ověření identity týmu na stanovišti.');

        $form->addGroup('Kontaktní údaje a přihlašovací heslo');
        $teamEmail = $form->addText('email', 'Kontaktní e-mail')
            ->setRequired('Vyplňte, prosím, kontaktní e-mail vašeho týmu.')
            ->addRule(Form::EMAIL, 'E-mail musí být ve správném tvaru.')
            ->addRule(Form::MAX_LENGTH, 'Kontaktní e-mail týmu může být maximálně 255 znaků dlouhý.', 255)
            ->setOption('description', 'Slouží ke komunikaci před hrou ohledně týmových záležitostí.');
        $phone = $form->addText('phone', 'Telefonní číslo')
            ->setRequired('Vyplňte, prosím, telefonní číslo.')
            ->addRule(Form::PATTERN, 'Telefonní číslo musí mít alespoň 9 číslic, například +420111222333.', '^[+]?[()/0-9. -]{9,}$')
            ->setOption('description', 'Slouží k nouzové komunikaci. Tento telefon mějte během hry u sebe.');
        $password = $form->addPassword('password', 'Heslo')
            ->setRequired('Vyplňte, prosím, heslo vašeho týmu.')
            ->addRule(Form::MIN_LENGTH, 'Délka hesla musí být alespoň 8 znaků.', 8);
        $password2 = $form->addPassword('password2', 'Heslo znovu')
            ->setRequired('Vyplňte, prosím, heslo pro kontrolu shody.')
            ->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $password);

        $form->addGroup('Členové')
            ->setOption('description', 'Pouze jméno prvního člena je povinné. Prosíme, abyste vyplnili údaje podle skutečnosti a v případě změn je příslušně upravili později v Nastavení týmu.');
        $members = $form->addContainer('members');
        foreach (range(1, 5) as $item) {
            $member = $members->addContainer($item);
            $fullname = $member->addText('fullname', 'Celé jméno')
                ->setRequired(false);
            $email = $member->addText('email', 'E-mail')
                ->setRequired(false)
                ->addRule(Form::EMAIL, 'E-mail musí být ve správném tvaru.');
            $age = $member->addText('age', 'Věk')
                ->setRequired(false)
                ->setType('number')
                ->addRule(Form::MIN, 'Minimální věk člena je 15 let. Jestli je vám méně na TMOU nemůžete. Pokud je vám méně než 18 musíte mít alespoň jednoho dospělého v týmu.', 15);
            $newsletter = $member->addCheckbox('addToNewsletter', 'Zahrnout do newsletteru Instruktorů Brno');

            // Conditional validations
            $fullname
                ->addConditionOn($email, Form::FILLED)
                ->setRequired('V případě, že vyplníte e-mail musíte vyplnit i jméno.');
            $fullname
                ->addConditionOn($age, Form::FILLED)
                ->setRequired('V případě, že vyplníte věk musíte vyplnit i jméno.');
            $fullname
                ->addConditionOn($newsletter, Form::FILLED)
                ->setRequired('V případě, že zaškrtnete přidání do newsletteru, musíte vyplnit i jméno.');
            $email
                ->addConditionOn($newsletter, Form::FILLED)
                ->setRequired('V případě, že zaškrtnete přidání do newsletteru, musíte vyplnit e-mail.');

            // Make name mandatory for the first team member
            if ($item === 1) {
                $fullname->setRequired('Jméno první člena musí být vyplněno.');
            }
        }

        if (!$registration && $showSelfreportedFee) {
            $form->addGroup('Sebereportované startovné');
            $feeOrganization = $form->addText('selfreportedFeeOrganization', 'Název organizace')
                ->setRequired(false);
            $feeAmount = $form->addText('selfreportedFeeAmount', 'Částka')
                ->setRequired(false)
                ->setOption('description', 'Prázdné, nebo částka v celých korunách.')
                ->addRule(Form::INTEGER, 'Zaplacená částka musí být nezáporná částka v celých korunách.')
                ->addRule(Form::MIN, 'Zaplacená částka musí být nezáporná částka v celých korunách.', 0);
            $feeAmount->setOption('description', 'Pokud chcete být zařazeni mezi zaplacené a hrající hráče, pak musí být zaplacená částka vyšší než startovné.');
            $feePublic = $form->addCheckbox('selfreportedFeePublic', 'Zveřejnit v seznamu týmů')
                ->setRequired(false);

            // Conditional validations
            $feeOrganization
                ->addConditionOn($feeAmount, Form::FILLED)
                ->setRequired('V případě, že vyplníte zaplacenou částku musíte vyplnit i název organizace.');
            $feeAmount
                ->addConditionOn($feeOrganization, Form::FILLED)
                ->setRequired('V případě, že vyplníte název organizace musíte vyplnit i zaplacenou částku.');
            $feeOrganization
                ->addConditionOn($feePublic, Form::FILLED)
                ->setRequired('V případě, že souhlasíte se zveřejněním v seznamu týmů musíte vyplnit i název organizace a zaplacenou částku.');
        }

        if (!$registration) {
            $form->addGroup('Potvrzení změn');
            $form->addPassword('oldPassword', 'Současné heslo')
                ->setRequired('Vyplňte, prosím své současné heslo.');
            $password->setOption('description', 'Vyplňte pouze pokud chcete heslo změnit.');
            $password->setRequired(false);
            $password->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $password2);
            $password2->setRequired(false);
        }

        if ($registration) {
            $form->addCaptcha('recaptcha', 'Ochrana před boty')
                ->setRequired('Ověřte, prosím, že jste člověk.');
        }

        if ($isAfterDeadline) {
            $name->setDisabled(true);
            $phrase->setDisabled(true);
            $phone->setDisabled(true);
            $teamEmail->setDisabled(true);
            if (isset($feeOrganization, $feeAmount, $feePublic)) {
                $feeOrganization->setDisabled(true);
                $feeAmount->setDisabled(true);
                $feePublic->setDisabled(true);
            }
        }

        $form->addPrimarySubmit('send', $registration ? 'Registrovat' : 'Uložit');
        $form->onSuccess[] = function (BaseForm $form, $values) use ($onSuccess): void {
            $onSuccess($form, $values);
        };
        $form->onError[] = function (BaseForm $form): void {
            if (count($form->getOwnErrors()) === 0) {
                $form->addError('Formulář obsahuje chyby, a proto nebyl uložen. Jednotlivé chyby naleznete u příslušných polí.');
            }
        };
        return $form;
    }
}
