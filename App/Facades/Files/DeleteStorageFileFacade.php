<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Facades\Files;

use InstruktoriBrno\TMOU\Services\Files\DeleteFileFromStorageDirectoryService;

class DeleteStorageFileFacade
{
    /** @var DeleteFileFromStorageDirectoryService */
    private $deleteFileFromStorageDirectoryService;

    public function __construct(
        DeleteFileFromStorageDirectoryService $deleteFileFromStorageDirectoryService
    ) {
        $this->deleteFileFromStorageDirectoryService = $deleteFileFromStorageDirectoryService;
    }

    /**
     * Delete givens file from given subdir in storage
     *
     * @param string $filename
     * @param string|null $subdir
     * @return bool
     *
     * @throws \InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileDeleteFailedException
     */
    public function __invoke(string $filename, ?string $subdir): bool
    {
        try {
            return ($this->deleteFileFromStorageDirectoryService)($filename, $subdir);
        } catch (\InstruktoriBrno\TMOU\Services\Files\Exceptions\FileDeleteFailedException $e) {
            throw new \InstruktoriBrno\TMOU\Facades\Files\Exceptions\FileDeleteFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
