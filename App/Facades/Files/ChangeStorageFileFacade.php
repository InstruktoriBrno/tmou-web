<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Files;

use InstruktoriBrno\TMOU\Services\Files\ChangeFileFromStorageDirectoryService;
use Nette\Utils\ArrayHash;

class ChangeStorageFileFacade
{
    /** @var ChangeFileFromStorageDirectoryService */
    private $changeFileFromStorageDirectoryService;

    public function __construct(
        ChangeFileFromStorageDirectoryService $changeFileFromStorageDirectoryService
    ) {
        $this->changeFileFromStorageDirectoryService = $changeFileFromStorageDirectoryService;
    }

    /**
     * Change givens file from given subdir in storage
     *
     * @param string|null $subdir
     * @param ArrayHash $values
     */
    public function __invoke(?string $subdir, ArrayHash $values): void
    {
        try {
            ($this->changeFileFromStorageDirectoryService)($values->original, $values->name, (string) $subdir, (string) $values->targetDir);
        } catch (\InstruktoriBrno\TMOU\Services\Files\Exceptions\FileMoveFailedException | \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileMoveFailedException($e->getMessage(), $e->getCode(), $e);
        } catch (\InstruktoriBrno\TMOU\Services\Files\Exceptions\DestinationFileAlreadyExistsException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileAlreadyExistsException($e->getMessage(), $e->getCode(), $e);
        } catch (\InstruktoriBrno\TMOU\Services\Files\Exceptions\SourceFileDoesNotExistsException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileNotFoundException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
