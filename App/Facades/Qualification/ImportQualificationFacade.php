<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Qualification;

use Doctrine\ORM\EntityManagerInterface;
use InstruktoriBrno\TMOU\Services\Events\FindEventService;
use InstruktoriBrno\TMOU\Services\Qualification\CreateQualificationService;
use InstruktoriBrno\TMOU\Services\Qualification\DeleteOldQualificationService;
use InstruktoriBrno\TMOU\Services\Qualification\ParseAndValidateQualificationService;
use Nette\Http\FileUpload;

class ImportQualificationFacade
{
    private EntityManagerInterface $entityManager;

    private FindEventService $findEventService;

    private ParseAndValidateQualificationService $parseAndValidateQualificationService;

    private DeleteOldQualificationService $deleteOldQualificationService;

    private CreateQualificationService $createQualificationService;


    public function __construct(
        EntityManagerInterface $entityManager,
        FindEventService $findEventService,
        ParseAndValidateQualificationService $parseAndValidateQualificationService,
        DeleteOldQualificationService $deleteOldQualificationService,
        CreateQualificationService $createQualificationService
    ) {

        $this->entityManager = $entityManager;
        $this->findEventService = $findEventService;
        $this->parseAndValidateQualificationService = $parseAndValidateQualificationService;
        $this->deleteOldQualificationService = $deleteOldQualificationService;
        $this->createQualificationService = $createQualificationService;
    }

    public function __invoke(int $eventId, FileUpload $specificationFile): void
    {
        $event = ($this->findEventService)($eventId);
        if ($event === null) {
            throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\NoSuchEventException;
        }

        if (!$specificationFile->isOk()) {
            throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\UploadFailedException;
        }
        try {
            $nodes = ($this->parseAndValidateQualificationService)($specificationFile);
        } catch (\InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException $exception) {
            throw new \InstruktoriBrno\TMOU\Facades\Qualification\Exceptions\InvalidXmlSchemaException($exception->getErrors());
        }

        // Drop previous qualification and all related data
        ($this->deleteOldQualificationService)($event);
        $this->entityManager->flush(); // needed due to constrains checks

        // Import new qualification via service
        ($this->createQualificationService)($nodes, $event);

        // Commit the change
        $this->entityManager->flush();
    }
}
