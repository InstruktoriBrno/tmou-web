<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Events;

use function assert;
use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Model\Event;

class FindEventForFormService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns given event as default values for form
     *
     * @param int $id
     *
     * @return array
     */
    public function __invoke(int $id): array
    {
        /** @var Event|null $object */
        $object = $this->entityManager->getRepository(Event::class)->find($id);
        assert($object !== null);
        return [
            'name' => $object->getName(),
            'number' => $object->getNumber(),
            'motto' => $object->getMotto(),
            'hasQualification' => $object->hasQualification(),
            'qualificationStart' => $object->getQualificationStart(),
            'qualificationEnd' => $object->getQualificationEnd(),
            'qualifiedTeamCount' => $object->getQualifiedTeamCount(),
            'registrationDeadline' => $object->getRegistrationDeadline(),
            'changeDeadline' => $object->getChangeDeadline(),
            'eventStart' => $object->getEventStart(),
            'eventEnd' => $object->getEventEnd(),
            'totalTeamCount' => $object->getTotalTeamCount(),
            'paymentPairingCodePrefix' => $object->getPaymentPairingCodePrefix(),
            'paymentPairingCodeSuffixLength' => $object->getPaymentPairingCodeSuffixLength(),
            'amount' => $object->getAmount(),
            'paymentDeadline' => $object->getPaymentDeadline(),
        ];
    }
}
