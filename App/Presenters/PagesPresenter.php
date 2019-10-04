<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\LoginContinueToIntents;
use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Facades\Teams\ChangeTeamFacade;
use InstruktoriBrno\TMOU\Facades\Teams\ConsumePasswordResetFacade;
use InstruktoriBrno\TMOU\Facades\Teams\CreateSSOSession;
use InstruktoriBrno\TMOU\Facades\Teams\InvalidateSSOSession;
use InstruktoriBrno\TMOU\Facades\Teams\RegisterTeamFacade;
use InstruktoriBrno\TMOU\Facades\Teams\RequestPasswordResetFacade;
use InstruktoriBrno\TMOU\Facades\Teams\TeamLoginFacade;
use InstruktoriBrno\TMOU\Forms\TeamForgottenPasswordFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamLoginFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamRegistrationFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamResetPasswordFormFactory;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Events\EventMacroDataProvider;
use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;
use InstruktoriBrno\TMOU\Services\Pages\FindPageInEventService;
use InstruktoriBrno\TMOU\Services\System\IsSLUGReservedService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamForFormService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsInEventService;
use InstruktoriBrno\TMOU\Services\Teams\TeamMacroDataProvider;
use Nette\Application\Routers\Route;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\Security\Identity;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;
use Tracy\ILogger;

final class PagesPresenter extends BasePresenter
{
    /** @var FindEventByNumberService @inject */
    public $findEventServiceByNumber;

    /** @var FindPageInEventService @inject */
    public $findPageInEventService;

    /** @var EventMacroDataProvider @inject */
    public $eventMacroDataProvider;

    /** @var TeamMacroDataProvider @inject */
    public $teamMacroDataProvider;

    /** @var TeamRegistrationFormFactory @inject */
    public $teamRegistrationFormFactory;

    /** @var RegisterTeamFacade @inject */
    public $registerTeamFacade;

    /** @var FindTeamsInEventService @inject */
    public $findTeamsInEventService;

    /** @var TeamLoginFormFactory @inject */
    public $teamLoginFormFactory;

    /** @var TeamLoginFacade @inject */
    public $teamLoginFacade;

    /** @var TeamForgottenPasswordFormFactory @inject */
    public $teamForgottenPasswordFormFactory;

    /** @var RequestPasswordResetFacade @inject */
    public $requestPasswordResetFacade;

    /** @var TeamResetPasswordFormFactory @inject */
    public $teamResetPasswordFormFactory;

    /** @var ConsumePasswordResetFacade @inject */
    public $consumePasswordResetFacade;

    /** @var FindTeamForFormService @inject */
    public $findTeamForFormService;

    /** @var ChangeTeamFacade @inject */
    public $changeTeamFacade;

    /** @var IsSLUGReservedService @inject */
    public $isSLUGReservedService;

    /** @var FindTeamService @inject */
    public $findTeamService;

    /** @var CreateSSOSession @inject */
    public $createSSOSession;

    /** @var InvalidateSSOSession @inject */
    public $invalidateSSOSession;


    /** @var Event|null */
    private $event;

    private function populateEventFromURL(?int $eventNumber = null): void
    {
        if ($eventNumber === null) {
            $eventNumber = $this->getParameter('eventNumber') !== null ? (int)$this->getParameter('eventNumber') : null;
        }
        if ($eventNumber !== null) {
            $this->event = ($this->findEventServiceByNumber)($eventNumber);
        }
        if ($this->event === null) {
            throw new \Nette\Application\BadRequestException("No such event with number [${eventNumber}].");
        }
    }

    private function enforceMatchingEvent(int $eventNumber): void
    {
        if (!$this->user->isInRole(UserRole::TEAM)) {
            throw new \Nette\Application\BadRequestException('Only teams are supposed to access this functionality', 403);
        }
        $identity = $this->user->getIdentity();
        if (!$identity instanceof Identity || !isset($identity->getData()['eventNumber']) || $identity->getData()['eventNumber'] !== $eventNumber) {
            throw new \Nette\Application\BadRequestException('Protected page only for teams from given event.', 403);
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionShow(?string $slug = null, ?int $eventNumber = null): void
    {
        if ($slug !== null && ($this->isSLUGReservedService)($slug)) {
            $request = $this->getRequest();
            assert($request !== null);
            $params = $request->getParameters();
            $params['action'] = Route::path2action($slug);
            unset($params['slug']);
            $request->setParameters($params);
            $this->forward($request);
            return;
        }
        $page = ($this->findPageInEventService)($slug, $eventNumber);
        if ($page === null) {
            throw new \Nette\Application\BadRequestException("No such page with SLUG [${slug}] within event with number [${eventNumber}].");
        }
        if (!$page->isRevealed($this->gameClockService->get()) && !$this->user->isAllowed(Resource::ADMIN_PAGES, Action::VIEW)) {
            throw new \Nette\Application\ForbiddenRequestException("Page with SLUG [${slug}] within event with number [${eventNumber}] was not yet revealed.");
        }
        if ($page->isCachingSafe()) {
            $this->eventMacroDataProvider->setEvent($page->getEvent()); // Needed due to page link creation
            $this->teamMacroDataProvider->setTeam(null);
        } else if ($page->getEvent() !== null) {
            $this->eventMacroDataProvider->setEvent($page->getEvent());
            if ($this->user->isLoggedIn() && $this->user->isInRole(UserRole::TEAM)) {
                $team = ($this->findTeamService)($this->user->getId());
                if ($team !== null && $team->getEvent()->getId() === $page->getEvent()->getNumber()) {
                    $this->teamMacroDataProvider->setTeam($team);
                }
            }
        }
        $this->template->page = $page;
        $this->template->event = $page->getEvent();
        $this->template->homepage = $homepage = $page->isDefault();
        if ($homepage) {
            assert(is_string(ReservedSLUG::UPDATES()->toScalar()));
            $this->template->updates = ($this->findPageInEventService)(ReservedSLUG::UPDATES()->toScalar(), $eventNumber);
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::QUALIFICATION) */
    public function actionQualification(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionQualificationStatistics(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionQualificationAnswers(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::LOGIN) */
    public function actionLogin(int $eventNumber, ?string $continueTo): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
        try {
            $this->template->continueToQualification = LoginContinueToIntents::fromScalar($continueTo ?? '')->equals(LoginContinueToIntents::QUALIFICATION());
        } catch (\Grifart\Enum\MissingValueDeclarationException $exception) {
            $this->template->continueToQualification = false;
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::CHANGE_DETAILS) */
    public function actionSettings(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->enforceMatchingEvent($eventNumber);
        $this->template->event = $event = $this->event;
        assert($event !== null);
        $this->template->now = $now = $this->gameClockService->get();
        $this->template->deadline = $deadline = $event->getChangeDeadlineComputed();
        $this->template->isOpen = $deadline !== null && $now < $deadline;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::LOGOUT) */
    public function actionLogout(int $eventNumber): void
    {
        $this->enforceMatchingEvent($eventNumber);
        ($this->invalidateSSOSession)();
        $this->user->logout(true);
        $this->flashMessage('Byli jste úspěšně odhlášeni.', Flash::SUCCESS);
        $this->redirect('Pages:login', $eventNumber);
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::REGISTER) */
    public function actionRegistration(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $event = $this->event;
        assert($event !== null);
        $this->template->now = $now = $this->gameClockService->get();
        $this->template->deadline = $deadline = $event->getRegistrationDeadline();
        $this->template->isOpen = $deadline !== null && $now < $deadline;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::FORGOTTEN_PASSWORD) */
    public function actionForgottenPassword(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::RESET_PASSWORD) */
    public function actionResetPassword(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionTeamsRegistered(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $event = $this->event;
        assert($event !== null);
        $this->template->teams = $this->findTeamsInEventService->findRegisteredTeams($event);
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionTeamsQualified(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $event = $this->event;
        assert($event !== null);
        $this->template->teams = $this->findTeamsInEventService->findQualifiedTeams($event);
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionTeamsPlaying(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $event = $this->event;
        assert($event !== null);
        $this->template->teams = $this->findTeamsInEventService->findPlayingTeams($event);
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionGameReports(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionGameStatistics(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionGameFlow(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
    }

    public function createComponentRegistrationForm(): Form
    {
        $this->populateEventFromURL();
        $event = $this->event;
        assert($event !== null);
        return $this->teamRegistrationFormFactory->create(function (Form $form, $values) use ($event) {
            if (!$this->user->isAllowed(Resource::TEAM_COMMON, Action::REGISTER)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }
            try {
                ($this->registerTeamFacade)($values, $event);
                $this->flashMessage('Váš tým byl úspěšně zaregistrován. Nyní se můžete přihlásit.', Flash::SUCCESS);
                $this->redirect('Pages:login', $event->getNumber());
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException $exception) {
                /** @var TextInput $input */
                $input = $form['name'];
                $input->addError('Jméno týmu je příliš dlouhé, maximum je 191 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\PhraseTooLongException $exception) {
                /** @var TextInput $input */
                $input = $form['phrase'];
                $input->addError('Tajná fráze týmu je příliš dlouhá, maximum je 255 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\EmailTooLongException $exception) {
                /** @var TextInput $input */
                $input = $form['email'];
                $input->addError('E-mail týmu je příliš dlouhý, maximum je 255 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateEmailInEventException $exception) {
                /** @var TextInput $input */
                $input = $form['email'];
                $input->addError('Tento e-mail týmu je již použit i jiného týmu.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateNameInEventException $exception) {
                /** @var TextInput $input */
                $input = $form['name'];
                $input->addError('Tým s tímto názvem je již zaregistrován.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TakenTeamNumberException $exception) {
                $form->addError('Došlo k souběhu více registrací, opakujte odeslání. V případě trvalého selhání napište organizátorům.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\OutOfRegistrationIntervalException $exception) {
                $this->flashMessage('Nenacházíte se v registračním období, registraci nelze dokončit.', Flash::DANGER);
                $this->redirect('this');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException $exception) {
                /** @var TextInput $input */
                $input = $form['password'];
                $input->addError('Heslo je příliš krátké, musí být alespoň 8 znaků dlouhé.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordException
                | \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamMemberException
                | \InstruktoriBrno\TMOU\Model\Exceptions\DuplicatedTeamMemberNumberException
                | \InstruktoriBrno\TMOU\Model\Exceptions\TeamMemberAlreadyBindedToTeamException $exception
            ) {
                Debugger::log($exception, ILogger::EXCEPTION);
                $form->addError('Registrace selhala, obraťte se prosím na organizátory.');
                return;
            }
        }, true, false);
    }

    public function createComponentSettingsForm(): Form
    {
        $this->populateEventFromURL();
        $event = $this->event;
        assert($event !== null);
        $form = $this->teamRegistrationFormFactory->create(function (Form $form, $values) use ($event) {
            if (!$this->user->isAllowed(Resource::TEAM_COMMON, Action::CHANGE_DETAILS)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }
            try {
                ($this->changeTeamFacade)($values, $this->user);
                $this->flashMessage('Údaje vašeho týmu byly úspěšně změněny.', Flash::SUCCESS);
                $this->redirect('Pages:settings', $event->getNumber());
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException $exception) {
                /** @var TextInput $input */
                $input = $form['name'];
                $input->addError('Jméno týmu je příliš dlouhé, maximum je 191 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\PhraseTooLongException $exception) {
                /** @var TextInput $input */
                $input = $form['phrase'];
                $input->addError('Tajná fráze týmu je příliš dlouhá, maximum je 255 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\EmailTooLongException $exception) {
                /** @var TextInput $input */
                $input = $form['email'];
                $input->addError('E-mail týmu je příliš dlouhý, maximum je 255 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateEmailInEventException $exception) {
                /** @var TextInput $input */
                $input = $form['email'];
                $input->addError('Tento e-mail týmu je již použit i jiného týmu.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\DuplicateNameInEventException $exception) {
                /** @var TextInput $input */
                $input = $form['name'];
                $input->addError('Tým s tímto názvem je již zaregistrován.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordException $exception) {
                $form->addError('Zadané současné heslo týmu je špatně.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\OutOfRegistrationIntervalException $exception) {
                $this->flashMessage('Některé údaje už nelze upravit, ostatní data byla úspěšně uložena.', Flash::WARNING);
                $this->redirect('this');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException $exception) {
                /** @var TextInput $input */
                $input = $form['password'];
                $input->addError('Heslo je příliš krátké, musí být alespoň 8 znaků dlouhé.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TakenTeamNumberException
                | \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamMemberException
                | \InstruktoriBrno\TMOU\Model\Exceptions\DuplicatedTeamMemberNumberException
                | \InstruktoriBrno\TMOU\Model\Exceptions\TeamMemberAlreadyBindedToTeamException $exception
            ) {
                Debugger::log($exception, ILogger::EXCEPTION);
                $form->addError('Úprava údajů selhala, obraťte se prosím na organizátory.');
                return;
            }
        },
            false,
            $event->getChangeDeadlineComputed() !== null && $event->getChangeDeadlineComputed() < $this->gameClockService->get());
        $data = ($this->findTeamForFormService)($this->user->getId());
        $form->setDefaults($data);

        return $form;
    }

    public function createComponentLoginForm(): Form
    {
        $this->populateEventFromURL();
        $event = $this->event;
        assert($event !== null);
        return $this->teamLoginFormFactory->create(function (Form $form, $values) use ($event) {
            if (!$this->user->isAllowed(Resource::TEAM_COMMON, Action::LOGIN)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }
            try {
                $team = ($this->teamLoginFacade)($event, $values->name, $values->password);
                ($this->createSSOSession)($team);
                $continueTo = $this->getParameter('continueTo');
                try {
                    if (LoginContinueToIntents::fromScalar($continueTo)->equals(LoginContinueToIntents::QUALIFICATION())) {
                        $this->redirectUrl('https://kvalifikace.tmou.cz');
                        return;
                    }
                } catch (\Grifart\Enum\MissingValueDeclarationException $exception) {
                    // intentionally no-op
                }
                $this->flashMessage('Byli jste úspěšně přihlášeni.', Flash::SUCCESS);
                $this->redirect('Pages:show', null, $event->getNumber());
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException $exception) {
                $form->addError('Tým tohoto jména není zaregistrován, zkontrolujte přesnost názvu týmu v seznamu zaregistrovaných týmů.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\InvalidTeamPasswordException $exception) {
                $form->addError('Heslo je chybné.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateSSOSessionException $e) {
                $this->flashMessage('Byli jste úspěšně přihlášeni zde, avšak nepodařilo se vás přihlásit do kvalifikačního systému a webinfa. Kontaktujte, prosím, organizátory.', Flash::WARNING);
                $this->redirect('Pages:show', null, $event->getNumber());
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\TeamLoginUnknownException $exception) {
                $form->addError('Přihlášení selhalo, kontaktujte prosím organizátory.');
                Debugger::log($exception, ILogger::EXCEPTION);
                return;
            }
        });
    }

    public function createComponentTeamForgottenPasswordForm(): Form
    {
        $this->populateEventFromURL();
        $event = $this->event;
        assert($event !== null);
        return $this->teamForgottenPasswordFormFactory->create(function (Form $form, ArrayHash $values) use ($event) {
            if (!$this->user->isAllowed(Resource::TEAM_COMMON, Action::FORGOTTEN_PASSWORD)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }
            try {
                ($this->requestPasswordResetFacade)($values->email, $event);
                $this->flashMessage('Vaše žádost o nové heslo byla uložena a na týmový e-mail byly odeslány další instrukce.', Flash::SUCCESS);
                $this->redirect('this');
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException $exception) {
                $form->addError('Tým s touto týmovou e-mailovou adresou nebyl nalezen, zkontrolujte překlepy a případně kontaktujte organizátory.');
                return;
            }
        });
    }

    public function createComponentTeamResetPasswordForm(): Form
    {
        $this->populateEventFromURL();
        $event = $this->event;
        assert($event !== null);
        return $this->teamResetPasswordFormFactory->create(function (Form $form, ArrayHash $values) use ($event) {
            if (!$this->user->isAllowed(Resource::TEAM_COMMON, Action::RESET_PASSWORD)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }
            try {
                ($this->consumePasswordResetFacade)($values->email, $values->password, $values->token, $event);
                $this->flashMessage('Vaše heslo bylo úspěšně změněno. Nyní se můžete přihlásit.', Flash::SUCCESS);
                $this->redirect('Pages:login', $event->getNumber());
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\NoSuchTeamException $exception) {
                $form->addError('Tým s touto týmovou e-mailovou adresou nebyl nalezen, zkontrolujte překlepy a případně kontaktujte organizátory.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\PasswordTooShortException $exception) {
                $form->addError('Zvolené heslo je příliš krátké, minimum je 8 znaků.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\InvalidPasswordResetTokenException $exception) {
                $form->addError('Tento kód je neplatný. Zkontrolujte úplnost.');
                return;
            } catch (\InstruktoriBrno\TMOU\Model\Exceptions\ExpiredPasswordResetTokenException $exception) {
                $form->addError('Tato žádost o nové heslo již expirovala, opakujte prosím vaši žádost.');
                return;
            }
        });
    }
}
