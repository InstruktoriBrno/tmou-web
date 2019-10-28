<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use function file_exists;
use function realpath;

class CreateNewDirectoryInStorageDirectoryService
{
    /**
     * Uploads given file(s) into given subdir
     *
     * @param string $newDirectoryName
     * @param string|null $subdir
     */
    public function __invoke(string $newDirectoryName, ?string $subdir): void
    {
        $storageDir = __DIR__ . '/../../../www/storage';
        $subpath = $storageDir . '/' . $subdir;
        if (!Strings::startsWith(realpath($subpath), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Subdir ${subdir} (${subpath} is outside storage dir.");
        }
        $newDirectoryPath = $subpath . '/' . $newDirectoryName;
        if (file_exists($newDirectoryPath)) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\NewDirectoryAlreadyExistsException("Directory {$subpath} already exists.");
        }
        try {
            FileSystem::createDir($newDirectoryPath);
        } catch (\Nette\IOException $exception) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\NewDirectoryCreationException("Creation of {$subpath} has failed.", 0, $exception);
        }
    }
}
