<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Events;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Enums\GameStatus;
use InstruktoriBrno\TMOU\Model\Event;
use InstruktoriBrno\TMOU\Services\Events\IsEventNumberUniqueService;
use InstruktoriBrno\TMOU\Services\Files\CreateNewDirectoryInStorageDirectoryService;
use InstruktoriBrno\TMOU\Services\Files\UploadToStorageDirectoryService;
use Nette\Http\FileUpload;
use Nette\Utils\ArrayHash;

class SaveEventFacade
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var IsEventNumberUniqueService */
    private $isEventNumberUniqueService;

    /** @var UploadToStorageDirectoryService */
    private $uploadToStorageDirectoryService;

    private CreateNewDirectoryInStorageDirectoryService $createNewDirectoryInStorageDirectoryService;

    public function __construct(
        EntityManagerInterface $entityManager,
        IsEventNumberUniqueService $eventNumberUniqueService,
        UploadToStorageDirectoryService $uploadToStorageDirectoryService,
        CreateNewDirectoryInStorageDirectoryService $createNewDirectoryInStorageDirectoryService
    ) {
        $this->entityManager = $entityManager;
        $this->isEventNumberUniqueService = $eventNumberUniqueService;
        $this->uploadToStorageDirectoryService = $uploadToStorageDirectoryService;
        $this->createNewDirectoryInStorageDirectoryService = $createNewDirectoryInStorageDirectoryService;
    }

    /**
     * @param ArrayHash $values
     * @param Event|null $event
     *
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\NameTooLongException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventNumberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingQualificationIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\ExcessQualificationIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventQualificationIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidEventIntervalException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingQualifiedTeamCountException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidTeamCountException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidAmountException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidRegistrationDeadlineException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentPairingCodePrefixException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentPairingCodeSuffixLengthException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPaymentPairingCodeSuffixLengthException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidPaymentPairingCodePrefixException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\ChangeDeadlineBeforeRegistrationDeadlineException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\InvalidChangeDeadlineException
     * @throws \InstruktoriBrno\TMOU\Facades\Events\Exceptions\NonUniqueEventNumberException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingPaymentDeadlineException
     * @throws \InstruktoriBrno\TMOU\Model\Exceptions\MissingAmountException
     *
     */
    public function __invoke(ArrayHash $values, ?Event $event): void
    {
        if ($event !== null) {
            $event->updateDetails(
                $values->name,
                (int) $values->number,
                (bool) $values->hasQualification,
                $values->qualificationStart,
                $values->qualificationEnd,
                $values->qualifiedTeamCount === '' ? null : (int) $values->qualifiedTeamCount,
                $values->registrationDeadline,
                $values->changeDeadline,
                $values->eventStart,
                $values->eventEnd,
                $values->totalTeamCount === '' ? null : (int) $values->totalTeamCount,
                $values->paymentPairingCodePrefix === '' ? null : $values->paymentPairingCodePrefix,
                $values->paymentPairingCodeSuffixLength === '' ? null : (int) $values->paymentPairingCodeSuffixLength,
                $values->amount === '' ? null : (int) $values->amount,
                $values->paymentDeadline,
                GameStatus::fromScalar($values->afterRegistrationTeamGameStatus),
                $values->selfreportedEntryFee,
                $values->sorting ? (float) $values->sorting : (float) $values->number,
            );
        } else {
            $event = new Event(
                $values->name,
                (int) $values->number,
                (bool) $values->hasQualification,
                $values->qualificationStart,
                $values->qualificationEnd,
                $values->qualifiedTeamCount === '' ? null : (int) $values->qualifiedTeamCount,
                $values->registrationDeadline,
                $values->changeDeadline,
                $values->eventStart,
                $values->eventEnd,
                $values->totalTeamCount === '' ? null : (int) $values->totalTeamCount,
                $values->paymentPairingCodePrefix === '' ? null : $values->paymentPairingCodePrefix,
                $values->paymentPairingCodeSuffixLength === '' ? null : (int) $values->paymentPairingCodeSuffixLength,
                $values->amount === '' ? null : (int) $values->amount,
                $values->paymentDeadline,
                GameStatus::fromScalar($values->afterRegistrationTeamGameStatus),
                $values->selfreportedEntryFee,
                $values->sorting ? (float) $values->sorting : null,
            );
        }
        if (!($this->isEventNumberUniqueService)($event)) {
            throw new \InstruktoriBrno\TMOU\Facades\Events\Exceptions\NonUniqueEventNumberException;
        }
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        if ($values->logo && $values->logo instanceof FileUpload && $values->logo->isOk()) {
            ($this->createNewDirectoryInStorageDirectoryService)((string) $event->getNumber(), null, true);
            $renamedFile = new FileUpload([
                'name' => 'logo.png',
                'size' => $values->logo->getSize(),
                'tmp_name' => $values->logo->getTemporaryFile(),
                'error' => $values->logo->getError(),
            ]);
            ($this->uploadToStorageDirectoryService)([$renamedFile], true, (string) $event->getNumber());
        }
    }
}
