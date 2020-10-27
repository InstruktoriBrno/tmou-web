<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Presenters;

use InstruktoriBrno\TMOU\Enums\Action;
use InstruktoriBrno\TMOU\Enums\Flash;
use InstruktoriBrno\TMOU\Enums\LoginContinueToIntents;
use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Enums\Resource;
use InstruktoriBrno\TMOU\Enums\UserRole;
use InstruktoriBrno\TMOU\Facades\Discussions\MarkThreadAsReadFacade;
use InstruktoriBrno\TMOU\Facades\Discussions\SaveNewPostFacade;
use InstruktoriBrno\TMOU\Facades\Discussions\SaveNewThreadFacade;
use InstruktoriBrno\TMOU\Facades\Discussions\SaveThreadFacade;
use InstruktoriBrno\TMOU\Facades\Discussions\ToggleHidePostFacade;
use InstruktoriBrno\TMOU\Facades\Discussions\ToggleLockThreadFacade;
use InstruktoriBrno\TMOU\Facades\Teams\ChangeTeamFacade;
use InstruktoriBrno\TMOU\Facades\Teams\ConsumePasswordResetFacade;
use InstruktoriBrno\TMOU\Facades\Teams\CreateSSOSession;
use InstruktoriBrno\TMOU\Facades\Teams\InvalidateSSOSession;
use InstruktoriBrno\TMOU\Facades\Teams\RegisterTeamFacade;
use InstruktoriBrno\TMOU\Facades\Teams\RequestPasswordResetFacade;
use InstruktoriBrno\TMOU\Facades\Teams\SaveTeamReviewFacade;
use InstruktoriBrno\TMOU\Facades\Teams\TeamLoginFacade;
use InstruktoriBrno\TMOU\Forms\ChangeThreadFormFactory;
use InstruktoriBrno\TMOU\Forms\NewPostFormFactory;
use InstruktoriBrno\TMOU\Forms\NewThreadFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamForgottenPasswordFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamLoginFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamRegistrationFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamResetPasswordFormFactory;
use InstruktoriBrno\TMOU\Forms\TeamReviewFormFactory;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Organizator;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Services\Discussions\FindCountsForThreads;
use InstruktoriBrno\TMOU\Services\Discussions\FindLastPostsForThreads;
use InstruktoriBrno\TMOU\Services\Discussions\FindPostService;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadAcknowledgementByThreadsAndUserService;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadForFormService;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadPostsService;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadService;
use InstruktoriBrno\TMOU\Services\Discussions\FindThreadsService;
use InstruktoriBrno\TMOU\Services\Events\EventMacroDataProvider;
use InstruktoriBrno\TMOU\Services\Events\FindEventByNumberService;
use InstruktoriBrno\TMOU\Services\Events\FindEventTeamReviewsService;
use InstruktoriBrno\TMOU\Services\Organizators\FindOrganizatorByIdService;
use InstruktoriBrno\TMOU\Services\Pages\FindPageInEventService;
use InstruktoriBrno\TMOU\Services\System\IsSLUGReservedService;
use InstruktoriBrno\TMOU\Services\System\RememberedNicknameService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamForFormService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamReviewForFormService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamService;
use InstruktoriBrno\TMOU\Services\Teams\FindTeamsInEventService;
use InstruktoriBrno\TMOU\Services\Teams\TeamMacroDataProvider;
use InstruktoriBrno\TMOU\Utils\SmallTexyFilter;
use Nette\Application\Routers\Route;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\TextInput;
use Nette\Security\Identity;
use Nette\Utils\ArrayHash;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Tracy\ILogger;
use function array_merge;

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

    /** @var TeamReviewFormFactory @inject */
    public $teamReviewFormFactory;

    /** @var SaveTeamReviewFacade @inject */
    public $saveTeamReviewFacade;

    /** @var FindTeamReviewForFormService @inject */
    public $findTeamReviewForFormService;

    /** @var FindEventTeamReviewsService @inject */
    public $findEventTeamReviewsService;

    /** @var NewThreadFormFactory @inject */
    public $newThreadFormFactory;

    /** @var ChangeThreadFormFactory @inject */
    public $changeThreadFormFactory;

    /** @var NewPostFormFactory @inject */
    public $newPostFormFactory;

    /** @var SaveNewThreadFacade @inject */
    public $saveNewThreadFacade;

    /** @var FindThreadsService @inject */
    public $findThreadsService;

    /** @var FindThreadService @inject */
    public $findThreadService;

    /** @var FindThreadPostsService @inject */
    public $findThreadPostsService;

    /** @var SaveNewPostFacade @inject */
    public $saveNewPostFacade;

    /** @var ToggleHidePostFacade @inject */
    public $toggleHidePostFacade;

    /** @var ToggleLockThreadFacade @inject */
    public $toggleLockThreadFacade;

    /** @var FindPostService @inject */
    public $findPostService;

    /** @var FindOrganizatorByIdService @inject */
    public $findOrganizatorService;

    /** @var MarkThreadAsReadFacade @inject */
    public $markThreadAsReadFacade;

    /** @var FindLastPostsForThreads @inject */
    public $findLastPostsForThreads;

    /** @var FindCountsForThreads @inject */
    public $findCountsForThreads;

    /** @var FindThreadAcknowledgementByThreadsAndUserService @inject */
    public $findThreadAcknowledgementByThreads;

    /** @var RememberedNicknameService @inject */
    public $rememberedNicknameService;

    /** @var FindThreadForFormService @inject */
    public $findThreadForFormService;

    /** @var SaveThreadFacade @inject */
    public $saveThreadFacade;

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
        } elseif ($page->getEvent() !== null) {
            $this->eventMacroDataProvider->setEvent($page->getEvent());
            if ($this->user->isLoggedIn() && $this->user->isInRole(UserRole::TEAM)) {
                $team = ($this->findTeamService)($this->user->getId());
                if ($team !== null && $team->getEvent()->getId() === $page->getEvent()->getId()) {
                    $this->teamMacroDataProvider->setTeam($team);
                    $validTeam = $team;
                }
            }
        }
        $this->template->page = $page;
        $this->template->event = $page->getEvent();
        $this->template->homepage = $homepage = $page->isDefault();
        if ($homepage) {
            assert(is_string(ReservedSLUG::UPDATES()->toScalar()));
            $this->template->updates = ($this->findPageInEventService)(ReservedSLUG::UPDATES()->toScalar(), $eventNumber);
            $this->template->showTeamReviewReminder = $page->getEvent() !== null
                && $page->getEvent()->isPeriodForRemindingTeamReviews($this->gameClockService->get())
                && isset($validTeam)
                && $validTeam->shouldFillTeamReview() === true;
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::LOGIN) */
    public function actionLogin(int $eventNumber, ?string $continueTo, ?string $backlink): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
        try {
            $this->template->continueToQualification = LoginContinueToIntents::fromScalar($continueTo ?? '')->equals(LoginContinueToIntents::QUALIFICATION());
            $this->template->continueToWebinfo = LoginContinueToIntents::fromScalar($continueTo ?? '')->equals(LoginContinueToIntents::WEBINFO());
        } catch (\Grifart\Enum\MissingValueDeclarationException $exception) {
            $this->template->continueToQualification = false;
            $this->template->continueToWebinfo = false;
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
        $this->template->hasRecaptcha = true;
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

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::TEAM_COMMON,InstruktoriBrno\TMOU\Enums\Action::CHANGE_REVIEW) */
    public function actionTeamReport(int $eventNumber): void
    {
        $this->enforceMatchingEvent($eventNumber);
        $this->populateEventFromURL($eventNumber);
        if ($this->user->isLoggedIn() && $this->user->isInRole(UserRole::TEAM)) {
            $team = ($this->findTeamService)($this->user->getId());
            $this->template->canFillTeamReview = $team !== null && $team->canFillTeamReview();
        }
        $this->template->event = $this->event;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionGameReports(int $eventNumber): void
    {
        $this->populateEventFromURL($eventNumber);
        $this->template->event = $this->event;
        assert($this->event !== null);
        $this->template->teamsWithReviews = ($this->findEventTeamReviewsService)($this->event);
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::PAGES,InstruktoriBrno\TMOU\Enums\Action::VIEW) */
    public function actionDiscussion(?int $eventNumber, ?int $thread, int $page = 0): void
    {
        if ($eventNumber !== null) {
            $this->populateEventFromURL($eventNumber);
        }
        $this->template->event = $this->event;
        $this->template->help = SmallTexyFilter::getSyntaxHelp();
        $this->template->currentPage = $page;
        $this->template->currentUserEntity = $this->getCurrentUserEntity();
        $this->template->isOrg = $isOrg = $this->user->isInRole(UserRole::ORG);
        $this->template->now = $now = $this->gameClockService->get();

        if ($thread !== null) {
            $threadEntity = ($this->findThreadService)($thread);
            if ($threadEntity === null) {
                throw new \Nette\Application\BadRequestException("No such thread with ID ${thread}.");
            }
            if ($threadEntity->isHidden($now) && !$isOrg) {
                throw new \Nette\Application\BadRequestException("Thread with ID ${thread} is hidden.", 403);
            }
            $this->template->thread = $threadEntity;
            $this->template->posts = ($this->findThreadPostsService)($threadEntity);
            $this->template->acks = ($this->findThreadAcknowledgementByThreads)([$threadEntity], $this->user);
            $this->setView('discussion.thread');
            if ($this->user->isAllowed(Resource::DISCUSSION, Action::MARK_THREAD_AS_READ)) {
                try {
                    ($this->markThreadAsReadFacade)($threadEntity, $this->user);
                } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchUserException $exception) {
                    Debugger::log($exception, ILogger::EXCEPTION);
                }
            }
        } else {
            $page = max(0, $page);
            $this->template->threadsLimit = $threadsLimit = 50;
            $this->template->threads = $threads = ($this->findThreadsService)($page, $threadsLimit, $isOrg);
            $this->template->threadsCounts = ($this->findCountsForThreads)($threads);
            $this->template->threadsLatestsPosts = ($this->findLastPostsForThreads)($threads);
            $this->template->acks = ($this->findThreadAcknowledgementByThreads)($threads, $this->user);
        }
    }

    /**
     * @return Organizator|Team|null
     */
    private function getCurrentUserEntity()
    {
        if ($this->user->isLoggedIn() && $this->user->isInRole(UserRole::ORG)) {
            return ($this->findOrganizatorService)($this->user->getId());
        }
        if ($this->user->isLoggedIn() && $this->user->isInRole(UserRole::TEAM)) {
            return ($this->findTeamService)($this->user->getId());
        }
        return null;
    }

    public function createComponentRegistrationForm(): Form
    {
        $this->populateEventFromURL();
        $event = $this->event;
        assert($event !== null);
        return $this->teamRegistrationFormFactory->create(function (Form $form, $values) use ($event): void {
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
        $form = $this->teamRegistrationFormFactory->create(function (Form $form, $values) use ($event): void {
            if (!$this->user->isAllowed(Resource::TEAM_COMMON, Action::CHANGE_DETAILS)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }
            try {
                $output = ($this->changeTeamFacade)($values, $this->user, $this->isImpersonated());
                if ($output === true) {
                    $this->flashMessage('Údaje vašeho týmu byly úspěšně změněny a stav vašeho týmu byl změněn jako zaplacený a hrající.', Flash::SUCCESS);
                } elseif ($output === false) {
                    $this->flashMessage(
                        'Údaje vašeho týmu byly úspěšně změněny avšak zaplacená částka je nižší než minimální startovné a tak váš tým nebyl nastaven jako zaplacený a hrající.',
                        Flash::WARNING
                    );
                } else {
                    $this->flashMessage('Údaje vašeho týmu byly úspěšně změněny.', Flash::SUCCESS);
                }

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
            $event->getChangeDeadlineComputed() !== null && $event->getChangeDeadlineComputed() < $this->gameClockService->get(),
            $event->isSelfreportedEntryFeeEnabled());
        $data = ($this->findTeamForFormService)($this->user->getId());
        $form->setDefaults($data);

        return $form;
    }

    public function createComponentLoginForm(): Form
    {
        $this->populateEventFromURL();
        $backlink = $this->getParameter('backlink');
        $event = $this->event;
        assert($event !== null);
        return $this->teamLoginFormFactory->create(function (Form $form, $values) use ($backlink, $event): void {
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
                    if (LoginContinueToIntents::fromScalar($continueTo)->equals(LoginContinueToIntents::WEBINFO())) {
                        $this->redirectUrl('https://webinfo.tmou.cz');
                        return;
                    }
                } catch (\Grifart\Enum\MissingValueDeclarationException $exception) {
                    // intentionally no-op
                }
                if ($backlink !== null) {
                    $this->restoreRequest($backlink);
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
        return $this->teamForgottenPasswordFormFactory->create(function (Form $form, ArrayHash $values) use ($event): void {
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
        return $this->teamResetPasswordFormFactory->create(function (Form $form, ArrayHash $values) use ($event): void {
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

    public function createComponentTeamReviewForm(): Form
    {
        $this->populateEventFromURL();
        $event = $this->event;
        assert($event !== null);
        $team = ($this->findTeamService)($this->user->getId());
        assert($team !== null);
        $form = $this->teamReviewFormFactory->create(function (Form $form, ArrayHash $values) use ($team): void {
            if (!$this->user->isAllowed(Resource::TEAM_COMMON, Action::CHANGE_REVIEW)) {
                $form->addError('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.');
                return;
            }

            try {
                ($this->saveTeamReviewFacade)($values, $team);
            } catch (\InstruktoriBrno\TMOU\Facades\Teams\Exceptions\CannotCreateTeamReviewBeforeEventEndException $e) {
                $form->addError('Před skončením hry nelze přidávat reportáže týmů.');
                return;
            }
            $this->flashMessage('Reportáž vašeho týmu byla úspěšně uložena', Flash::SUCCESS);
            $this->redirect('this');
        });
        $defaults = ($this->findTeamReviewForFormService)($team);
        $form->setDefaults($defaults);
        return $form;
    }

    public function createComponentNewThreadForm(): Form
    {
        $form = $this->newThreadFormFactory->create(function (Form $form, ArrayHash $values): void {
            if (!$this->user->isAllowed(Resource::DISCUSSION, Action::NEW_THREAD)) {
                $this->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                return;
            }
            try {
                $thread = ($this->saveNewThreadFacade)($values, $this->getUser());
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\TitleIsTooLongException $e) {
                $form->addError('Název nového vlákna může být maximálně 191 znaků dlouhý.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException $e) {
                $this->flashMessage('V tomto vlákně již nelze přidávat další příspěvky, to lze pouze půl rok po skončení příslušného ročníku.', Flash::WARNING);
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchEventException $e) {
                $form->addError('Vybraný ročník neexistuje. Kontaktujte, prosím, správce.');
                return;
            }
            $this->flashMessage('Nové vlákno bylo úspěšně založeno.', Flash::SUCCESS);
            $this->redirect('this', ['thread' => $thread->getId()]);
        }, $this->user->isInRole(UserRole::ORG));
        $defaults = ['nickname' => $this->rememberedNicknameService->get()];
        if ($this->event !== null) {
            $defaults = array_merge($defaults, ['event' => $this->event->getId()]);
        }
        $form->setDefaults($defaults);
        return $form;
    }

    public function createComponentChangeThreadForm(): Form
    {
        $threadId = $this->getParameter('thread');
        if ($threadId === null || !Validators::isNumericInt($threadId)) {
            throw new \Nette\Application\BadRequestException("Invalid thread ${threadId}.");
        }
        $form = $this->changeThreadFormFactory->create(function (Form $form, ArrayHash $values) use ($threadId): void {
            if (!$this->user->isAllowed(Resource::DISCUSSION, Action::CHANGE_THREAD)) {
                $this->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                return;
            }
            try {
                $thread = ($this->saveThreadFacade)($values, (int) $threadId);
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\TitleIsTooLongException $e) {
                $form->addError('Název vlákna může být maximálně 191 znaků dlouhý.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException $e) {
                $this->flashMessage('Toto vlákno již nelze měnit, to lze pouze půl rok po skončení příslušného ročníku.', Flash::WARNING);
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchEventException $e) {
                $form->addError('Vybraný ročník neexistuje. Kontaktujte, prosím, správce.');
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\NoSuchThreadException $e) {
                $form->addError('Vybrané vlákno neexistuje. Kontaktujte, prosím, správce.');
                return;
            }
            $this->flashMessage('Vlákno bylo úspěšně upraveno.', Flash::SUCCESS);
            $this->redirect('this', ['thread' => $thread->getId()]);
        });
        $form->setDefaults(($this->findThreadForFormService)((int) $threadId));
        return $form;
    }

    public function createComponentNewPostForm(): Form
    {
        $thread = $this->getParameter('thread');
        if ($thread === null || !Validators::isNumericInt($thread)) {
            throw new \Nette\Application\BadRequestException("Invalid thread ${thread}.");
        }
        $threadEntity = ($this->findThreadService)((int) $thread);
        if ($threadEntity === null) {
            throw new \Nette\Application\BadRequestException("No such thread with ID ${thread}.");
        }
        $form = $this->newPostFormFactory->create(function (Form $form, ArrayHash $values) use ($threadEntity): void {
            if (!$this->user->isAllowed(Resource::DISCUSSION, Action::NEW_POST)) {
                $this->flashMessage('Nejste oprávněni provádět tuto operaci. Pokud věříte, že jde o chybu, kontaktujte správce.', Flash::DANGER);
                return;
            }
            try {
                $post = ($this->saveNewPostFacade)($threadEntity, $values, $this->getUser());
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsClosedException $e) {
                $this->flashMessage('V tomto vlákně již nelze přidávat další příspěvky, to lze pouze půl rok po skončení příslušného ročníku.', Flash::WARNING);
                return;
            } catch (\InstruktoriBrno\TMOU\Facades\Discussions\Exceptions\EventIsLockedException $e) {
                $this->flashMessage('V tomto vlákně již nelze přidávat další příspěvky protože bylo organizátory uzamčeno.', Flash::WARNING);
                return;
            }
            $this->flashMessage('Nový příspěvek byl úspěšně přidán.', Flash::SUCCESS);
            $this->redirect('this#end', ['thread' => $post->getThread()->getId()]);
        }, $this->user->isInRole(UserRole::ORG));
        $defaults = ['nickname' => $this->rememberedNicknameService->get()];
        if ($this->event !== null) {
            $defaults = array_merge($defaults, ['event' => $this->event->getId()]);
        }
        $form->setDefaults($defaults);
        return $form;
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::DISCUSSION,InstruktoriBrno\TMOU\Enums\Action::HIDE_POST) */
    public function handleHidePost(int $postId): void
    {
        $post = ($this->findPostService)($postId);
        if ($post === null) {
            throw new \Nette\Application\BadRequestException("No such post with ID ${postId}.");
        }
        ($this->toggleHidePostFacade)($post);
        if ($this->isAjax()) {
            $this->redrawControl('posts');
        } else {
            if ($post->isHidden()) {
                $this->flashMessage('Příspěvek byl úspěšně skryt.', Flash::SUCCESS);
            } else {
                $this->flashMessage('Příspěvek byl úspěšně odskryt.', Flash::SUCCESS);
            }
            $this->redirect('this');
        }
    }

    /** @privilege(InstruktoriBrno\TMOU\Enums\Resource::DISCUSSION,InstruktoriBrno\TMOU\Enums\Action::LOCK_THREAD) */
    public function handleLockThread(int $threadId): void
    {
        $thread = ($this->findThreadService)($threadId);
        if ($thread === null) {
            throw new \Nette\Application\BadRequestException("No such thread with ID ${threadId}.");
        }
        ($this->toggleLockThreadFacade)($thread);
        if ($this->isAjax()) {
            $this->redrawControl('form');
        } else {
            if ($thread->isLocked()) {
                $this->flashMessage('Vlákno bylo úspěšně uzamčeno.', Flash::SUCCESS);
            } else {
                $this->flashMessage('Vlákno bylo úspěšně odemčeno.', Flash::SUCCESS);
            }
            $this->redirect('this');
        }
    }

    public function computeGrayscale(?Event $event): int
    {
        if ($event === null) {
            return 100;
        }
        $nowYear = (int) $this->gameClockService->get()->format('Y');
        $eventYear = (int) $event->getEventStart()->format('Y');
        if ($nowYear === $eventYear) {
            return 100;
        }
        if ($nowYear - 1 === $eventYear) {
            return 90;
        }
        if ($nowYear - 2 === $eventYear) {
            return 80;
        }
        return 70;
    }
}
