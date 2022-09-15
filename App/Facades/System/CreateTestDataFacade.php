<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\System;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Enums\ReservedSLUG;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\MenuItem;
use InstruktoriBrno\TMOU\Model\Page;
use InstruktoriBrno\TMOU\Model\Post;
use InstruktoriBrno\TMOU\Model\Team;
use InstruktoriBrno\TMOU\Model\TeamMember;
use InstruktoriBrno\TMOU\Model\Thread;
use InstruktoriBrno\TMOU\Services\Events\FindDefaultEventValuesForFormService;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use function assert;
use function date;

class CreateTestDataFacade
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var FindDefaultEventValuesForFormService */
    private $findDefaultEventValuesForFormService;

    public function __construct(
        EntityManagerInterface $entityManager,
        FindDefaultEventValuesForFormService $findDefaultEventValuesForFormService
    ) {

        $this->entityManager = $entityManager;
        $this->findDefaultEventValuesForFormService = $findDefaultEventValuesForFormService;
    }

    public function __invoke(): void
    {
        // Check if database is without events and pages
        $events = $this->entityManager->getRepository(Event::class)->findAll();
        $pages = $this->entityManager->getRepository(Page::class)->findAll();
        $menuItems = $this->entityManager->getRepository(MenuItem::class)->findAll();

        if (count($events) !== 0 || count($pages) !== 0 || count($menuItems) !== 0) {
            throw new \InstruktoriBrno\TMOU\Facades\System\Exceptions\NonEmptyDatabaseException;
        }

        // Ensure event
        $event = $this->getEvent();
        $this->entityManager->persist($event);

        // Ensure some event pages (homepage, about)
        $this->entityManager->persist($homePage = $this->getHomepagePage($event));
        $this->entityManager->persist($aboutPage = $this->getAboutPage($event));
        $this->entityManager->persist($rulesPage = $this->getRulesPage($event));
        $this->entityManager->persist($qualificationRulesPage = $this->getQualificationRulesPage($event));
        $this->entityManager->persist($wildCardsPage = $this->getWildCardsPage($event));
        $this->entityManager->persist($qualificationResultsPage = $this->getQualificationResultsPage($event));

        // Ensure menu items in general block
        $this->entityManager->persist($this->getIndexMenuItem($event, $homePage));
        $this->entityManager->persist($this->getAboutMenuItem($event, $aboutPage));

        // Ensure menu items in team block
        $this->entityManager->persist($this->getLoginMenuItem($event));
        $this->entityManager->persist($this->getRegistrationMenuItem($event));
        $this->entityManager->persist($this->getTeamSettingsMenuItem($event));
        $this->entityManager->persist($this->getRegisteredTeamsMenuItem($event));
        $this->entityManager->persist($this->getLogoutMenuItem($event));

        // Ensure menu items in qualification block
        $this->entityManager->persist($this->getQualificationRulesMenuItem($event, $qualificationRulesPage));
        $this->entityManager->persist($this->getQualificationSystemMenuItem($event));
        $this->entityManager->persist($this->getQualificationResultsMenuItem($event, $qualificationResultsPage));
        $this->entityManager->persist($this->getWildCardsMenuItem($event, $wildCardsPage));
        $this->entityManager->persist($this->getQualifiedTeamsMenuItem($event));
        $this->entityManager->persist($this->getQualificationStatisticsMenuItem($event));
        $this->entityManager->persist($this->getQualificationAnswersMenuItem($event));

        // Ensure menu items in game block
        $this->entityManager->persist($this->getRulesMenuItem($event, $rulesPage));
        $this->entityManager->persist($this->getPlayingTeamsMenuItem($event));
        $this->entityManager->persist($this->getTMOUInfoMenuItem($event));
        $this->entityManager->persist($this->getTeamReportMenuItem($event));
        $this->entityManager->persist($this->getGameReportsMenuItem($event));
        $this->entityManager->persist($this->getGameStatisticsItem($event));
        $this->entityManager->persist($this->getGameFlowItem($event));

        // Ensure menu items in general other block and to block without year
        $this->entityManager->persist($this->getDiscussionMenuItem($event));
        $this->entityManager->persist($this->getDiscussionMenuItem(null));

        // Ensure test teams
        $this->entityManager->persist($team1 = $this->getFirstTeam($event));
        $this->entityManager->persist($team2 = $this->getSecondTeam($event));
        $this->entityManager->persist($team3 = $this->getThirdTeam($event));
        $this->entityManager->persist($team4 = $this->getFourthTeam($event));

        // Ensure discussion posts
        Arrays::map($this->getFirstThread($event, $team1), [$this->entityManager, 'persist']);
        Arrays::map($this->getSecondThread($event, $team2, $team3), [$this->entityManager, 'persist']);

        $this->entityManager->flush();
    }

    private function getEvent(): Event
    {
        $values = ArrayHash::from(($this->findDefaultEventValuesForFormService)());
        $values->name = ((int) date('y')) + 2; // 21 was in 2019
        $values->number = ((int) date('y')) + 2; // 21 was in 2019
        $values->paymentPairingCodePrefix = '1190160';
        $values->paymentPairingCodeSuffixLength = 3;
        $event = new Event(
            (string) $values->name,
            $values->number,
            (bool) $values->hasQualification,
            $values->qualificationStart,
            $values->qualificationEnd,
            $values->qualifiedTeamCount === '' ? null : (int) $values->qualifiedTeamCount,
            $values->registrationDeadline,
            $values->changeDeadline,
            $values->eventStart,
            $values->eventEnd,
            $values->totalTeamCount === '' ? null : (int) $values->totalTeamCount,
            $values->paymentPairingCodePrefix,
            $values->paymentPairingCodeSuffixLength,
            $values->amount,
            $values->paymentDeadline,
            GameStatus::REGISTERED(),
        );
        return $event;
    }

    // ==== Pages ============================================

    private function getHomepagePage(Event $event): Page
    {
        return new Page(
            'Úvod',
            'index',
            'Úvod',
            $event,
            'Toto je úvodní testovací stránka',
            false,
            false,
            true,
            null
        );
    }

    private function getAboutPage(Event $event): Page
    {
        return new Page(
            'O hře',
            'about',
            'O hře TMOU',
            $event,
            'Toto je stránka vysvětlující co je TMOU.',
            false,
            false,
            false,
            null
        );
    }

    private function getQualificationRulesPage(Event $event): Page
    {
        return new Page(
            'Pravidla kvalifikace',
            'qualification-rules',
            'Pravidla kvalifikace',
            $event,
            'Toto je stránka s pravidly kvalifikace.',
            false,
            false,
            false,
            null
        );
    }

    private function getQualificationResultsPage(Event $event): Page
    {
        return new Page(
            'Výsledky kvalifikace',
            'qualification-results',
            'Výsledky kvalifikace',
            $event,
            'Toto je stránka s výsledky kvalifikace.',
            true,
            false,
            false,
            null
        );
    }

    private function getRulesPage(Event $event): Page
    {
        return new Page(
            'Pravidla hry',
            'rules',
            'Pravidla hry',
            $event,
            'Toto je stránka s pravidly hry.',
            false,
            false,
            false,
            null
        );
    }

    private function getWildCardsPage(Event $event): Page
    {
        return new Page(
            'Divoké karty',
            'wild-cards',
            'Divoké karty',
            $event,
            'Toto je stránka s pravidly divokých karet.',
            false,
            false,
            false,
            null
        );
    }

    // ==== Menu Items ============================================

    public function getIndexMenuItem(Event $event, Page $homePage): MenuItem
    {
        $menuItem1 = new MenuItem(
            $event,
            'Úvod',
            null,
            null,
            'a',
            null,
            1,
            $homePage,
            null,
            null,
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem1;
    }

    public function getAboutMenuItem(Event $event, Page $aboutPage): MenuItem
    {
        $menuItem2 = new MenuItem(
            $event,
            'O hře',
            null,
            null,
            'a',
            null,
            2,
            $aboutPage,
            null,
            null,
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem2;
    }


    public function getLoginMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Přihlášení',
            null,
            null,
            'b',
            'Tým',
            1,
            null,
            $event,
            ReservedSLUG::LOGIN(),
            null,
            true,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }


    public function getRegistrationMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Registrace',
            null,
            null,
            'b',
            'Tým',
            2,
            null,
            $event,
            ReservedSLUG::REGISTRATION(),
            null,
            true,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }

    public function getTeamSettingsMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Nastavení týmu',
            null,
            null,
            'b',
            'Tým',
            3,
            null,
            $event,
            ReservedSLUG::SETTINGS(),
            null,
            false,
            false,
            true,
            null,
            null
        );
        return $menuItem;
    }

    public function getRegisteredTeamsMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Registrované týmy',
            null,
            null,
            'b',
            'Tým',
            4,
            null,
            $event,
            ReservedSLUG::TEAMS_REGISTERED(),
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }

    public function getLogoutMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Odhlásit',
            null,
            null,
            'b',
            'Tým',
            5,
            null,
            $event,
            ReservedSLUG::LOGOUT(),
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }

    public function getQualificationRulesMenuItem(Event $event, Page $page): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Pravidla',
            null,
            null,
            'c',
            'Kvalifikace',
            0,
            $page,
            null,
            null,
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }

    public function getQualificationSystemMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Kvalifikační systém',
            null,
            null,
            'c',
            'Kvalifikace',
            1,
            null,
            null,
            null,
            'https://kvalifikace.tmou.cz',
            false,
            false,
            false,
            $event->getQualificationStart(),
            $event->getQualificationEnd()
        );
        return $menuItem;
    }

    public function getQualificationResultsMenuItem(Event $event, Page $page): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Výsledky kvalifikace',
            null,
            null,
            'c',
            'Kvalifikace',
            2,
            $page,
            null,
            null,
            null,
            false,
            false,
            false,
            $event->getEventEnd(),
            null
        );
        return $menuItem;
    }

    public function getWildCardsMenuItem(Event $event, Page $page): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Divoké karty',
            null,
            null,
            'c',
            'Kvalifikace',
            3,
            $page,
            null,
            null,
            null,
            false,
            false,
            false,
            $event->getQualificationEnd(),
            null
        );
        return $menuItem;
    }

    public function getQualifiedTeamsMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Kvalifikované týmy',
            null,
            null,
            'c',
            'Kvalifikace',
            4,
            null,
            $event,
            ReservedSLUG::TEAMS_QUALIFIED(),
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }

    public function getQualificationStatisticsMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Statistiky kvalifikace',
            null,
            null,
            'c',
            'Kvalifikace',
            5,
            null,
            $event,
            ReservedSLUG::QUALIFICATION_STATISTICS(),
            null,
            false,
            false,
            false,
            $event->getEventEnd(),
            null
        );
        return $menuItem;
    }

    public function getQualificationAnswersMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Statistika odpovědí',
            null,
            null,
            'c',
            'Kvalifikace',
            6,
            null,
            $event,
            ReservedSLUG::QUALIFICATION_ANSWERS(),
            null,
            false,
            false,
            false,
            $event->getEventEnd(),
            null
        );
        return $menuItem;
    }

    public function getRulesMenuItem(Event $event, Page $page): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Pravidla',
            null,
            null,
            'd',
            'Hra',
            0,
            $page,
            null,
            null,
            null,
            false,
            false,
            false,
            $event->getQualificationEnd(),
            null
        );
        return $menuItem;
    }

    public function getPlayingTeamsMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Hrající týmy',
            null,
            null,
            'd',
            'Hra',
            1,
            null,
            $event,
            ReservedSLUG::TEAMS_PLAYING(),
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }

    public function getTMOUInfoMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Herní systém',
            null,
            null,
            'd',
            'Hra',
            2,
            null,
            null,
            null,
            'https://webinfo.tmou.cz',
            false,
            false,
            false,
            $event->getEventStart(),
            $event->getEventEnd()
        );
        return $menuItem;
    }

    public function getTeamReportMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Reportáž týmu',
            null,
            null,
            'd',
            'Hra',
            3,
            null,
            $event,
            ReservedSLUG::TEAM_REPORT(),
            null,
            false,
            false,
            false,
            $event->getEventEnd(),
            null
        );
        return $menuItem;
    }

    public function getGameReportsMenuItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Reportáže týmů',
            null,
            null,
            'd',
            'Hra',
            4,
            null,
            $event,
            ReservedSLUG::GAME_REPORTS(),
            null,
            false,
            false,
            false,
            $event->getEventEnd(),
            null
        );
        return $menuItem;
    }

    public function getGameStatisticsItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Statistiky hry',
            null,
            null,
            'd',
            'Hra',
            5,
            null,
            $event,
            ReservedSLUG::GAME_STATISTICS(),
            null,
            false,
            false,
            false,
            $event->getEventEnd(),
            null
        );
        return $menuItem;
    }

    public function getGameFlowItem(Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Průběh hry',
            null,
            null,
            'd',
            'Hra',
            6,
            null,
            $event,
            ReservedSLUG::GAME_FLOW(),
            null,
            false,
            false,
            false,
            $event->getEventEnd(),
            null
        );
        return $menuItem;
    }

    public function getDiscussionMenuItem(?Event $event): MenuItem
    {
        $menuItem = new MenuItem(
            $event,
            'Fórum',
            null,
            null,
            'e',
            null,
            0,
            null,
            null,
            ReservedSLUG::DISCUSSION(),
            null,
            false,
            false,
            false,
            null,
            null
        );
        return $menuItem;
    }

    public function getFirstTeam(Event $event): Team
    {
        assert($event->getRegistrationDeadline() !== null);
        $team = new Team(
            $event,
            1,
            'Team 1',
            'team1@example.com',
            'testtest',
            'We were first!',
            '111222333',
            $event->getRegistrationDeadline()->modify('-4 months'),
            [
                new TeamMember(1, 'First Player', null, null, false),
            ]
        );
        $team->touchLoggedAt($event->getRegistrationDeadline()->modify('-4 months'));
        $team->changeTeamGameStatus(GameStatus::PLAYING());
        $team->markAsPaid($event->getRegistrationDeadline()->modify('-2 months'));
        return $team;
    }

    public function getSecondTeam(Event $event): Team
    {
        assert($event->getRegistrationDeadline() !== null);
        $team = new Team(
            $event,
            2,
            'Team 2',
            'team2@example.com',
            'testtest',
            'We were second!',
            '222333111',
            $event->getRegistrationDeadline()->modify('-2 months'),
            [
                new TeamMember(1, 'First', 'email1@example.com', 18, true),
                new TeamMember(2, 'Second', 'email2@example.com', 20, true),
            ]
        );
        $team->touchLoggedAt($event->getRegistrationDeadline()->modify('-2 months'));
        $team->changeTeamGameStatus(GameStatus::PLAYING());
        $team->markAsPaid($event->getRegistrationDeadline()->modify('-1 months'));
        return $team;
    }

    public function getThirdTeam(Event $event): Team
    {
        assert($event->getRegistrationDeadline() !== null);
        $team = new Team(
            $event,
            3,
            'Team 3',
            'team3@example.com',
            'testtest',
            'We were third!',
            '333111222',
            $event->getRegistrationDeadline()->modify('-2 months'),
            [
                new TeamMember(1, 'Great', 'great@example.com', 55, false),
            ]
        );
        $team->touchLoggedAt($event->getRegistrationDeadline()->modify('-2 months'));
        $team->changeTeamGameStatus(GameStatus::QUALIFIED());
        return $team;
    }

    public function getFourthTeam(Event $event): Team
    {
        assert($event->getRegistrationDeadline() !== null);
        $team = new Team(
            $event,
            4,
            'Team 4',
            'team4@example.com',
            'testtest',
            'We were fourth!',
            '000111222333',
            $event->getRegistrationDeadline()->modify('-1 months'),
            [
                new TeamMember(1, 'Awesome', 'awesome@example.com', null, false),
            ]
        );
        return $team;
    }

    /**
     * @param Event $event
     * @param Team $team1
     * @return array{0: Thread, 1: Post, 2: Post}
     */
    public function getFirstThread(Event $event, Team $team1): array
    {
        $thread = new Thread($event, 'První diskuze', null, $team1, false);
        $post1 = new Post($thread, 'První příspěvek', 'Josef', null, $team1, false);
        $post2 = new Post($thread, 'Druhý příspěvek', null, null, $team1, false);
        return [$thread, $post1, $post2];
    }

    /**
     * @param Event $event
     * @param Team $team2
     * @param Team $team3
     * @return array{0: Thread, 1: Post, 2: Post, 3: Post}
     */
    public function getSecondThread(Event $event, Team $team2, Team $team3): array
    {
        $thread = new Thread($event, 'Druhá diskuze', null, $team2, false);
        $post1 = new Post($thread, 'První příspěvek', 'Josef', null, $team2, false);
        $post2 = new Post($thread, 'Druhý příspěvek', null, null, $team3, false);
        $post3 = new Post($thread, 'Můžete použít, **tučný** řez písma, *kurzívu* nebo ***obojí***.
"Text odkazu":https://www.tmou.cz/ nebo jen URL https://www.tmou.cz

- nečíslovaný
- seznam

1. číslovaný
2. seznam

> Toto jsou dlouhé citace odsazené jedním znakem >

Krátké citace můžete udělat pomocí >>takto<<. Referenci na jiný příspěvek vložíte pořadového čísla: [1].
Hodit se též může horní index^^takhle^^ nebo index^2, také spodní index__takhle__ nebo index_2.', null, null, $team3, false);
        return [$thread, $post1, $post2, $post3];
    }
}
