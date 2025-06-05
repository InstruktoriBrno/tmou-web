<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use DOMElement;
use DOMNode;
use DOMNodeList;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Model\Level;
use InstruktoriBrno\TMOU\Model\Password;
use InstruktoriBrno\TMOU\Model\Puzzle;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;

class CreateQualificationService
{
    private EntityManagerInterface $entityManager;

    private FindEventService $findEventService;

    public function __construct(EntityManagerInterface $entityManager, FindEventService $findEventService)
    {
        $this->entityManager = $entityManager;
        $this->findEventService = $findEventService;
    }

    /**
     * Hydrate qualification objects from given XML nodes
     *
     * @param array{maxNumberOfAnswers: DOMNode, secondsPenalizationAfterIncorrectAnswer: DOMNode, levels: DOMNodeList<DOMElement>} $qualificationNodes
     */
    public function __invoke(array $qualificationNodes, Event $event): void
    {
        $event = ($this->findEventService)($event->getId());
        [
            'maxNumberOfAnswers' => $maxNumberOfAnswers,
            'secondsPenalizationAfterIncorrectAnswer' => $secondsPenalizationAfterIncorrectAnswer,
            'levels' => $levels,
        ] = $qualificationNodes;

        $objectToPersist = [];
        $event->updateQualificationDetails(
            (int) $maxNumberOfAnswers->nodeValue,
            $maxNumberOfAnswers->getAttribute('show') === 'true', // @phpstan-ignore-line
            (int) $secondsPenalizationAfterIncorrectAnswer->nodeValue,
            // @phpstan-ignore-next-line
            $secondsPenalizationAfterIncorrectAnswer->getAttribute('show') === 'true'
        );
        $objectToPersist[] = $event;
        $i = 1;
        foreach ($levels as $level) {
            $last = $i === $levels->length;
            $i++;
            if ($last) {
                $levelEntity = new Level(
                    $event,
                    (int) $level->getAttribute('index'),
                    null,
                    null,
                    null,
                );
                $objectToPersist[] = $levelEntity;
                continue;
            }
            $link = $level->getElementsByTagName('link')->item(0);
            $backupLink = $level->getElementsByTagName('backup-link')->item(0);
            $codesNeeded = $level->getElementsByTagName('codes-needed')->item(0);
            $puzzles = $level->getElementsByTagName('puzzles')->item(0)->getElementsByTagName('puzzle');

            $levelEntity = new Level(
                $event,
                (int) $level->getAttribute('index'),
                $link->nodeValue,
                $backupLink->nodeValue,
                (int) $codesNeeded->nodeValue,
            );
            $objectToPersist[] = $levelEntity;
            foreach ($puzzles as $puzzle) {
                $puzzleEntity = new Puzzle($levelEntity, $puzzle->getAttribute('name'));
                $passwords = $puzzle->getElementsByTagName('password');
                $passwordsArray = [];
                foreach ($passwords as $password) {
                    $passwordEntity = new Password($puzzleEntity, $password->nodeValue);
                    $objectToPersist[] = $passwordEntity;
                    $passwordsArray[] = $passwordEntity;
                }
                $puzzleEntity->setPasswords($passwordsArray);
                $objectToPersist[] = $puzzleEntity;
            }
        }

        foreach ($objectToPersist as $object) {
            $this->entityManager->persist($object);
        }
    }
}
