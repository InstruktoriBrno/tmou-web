<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Utils\FileSystem;
use function file_exists;
use function realpath;

class ChangeFileFromStorageDirectoryService
{
    /**
     * Change given file in given subdir of storage
     *
     * @param string $oldFilename
     * @param string $newFilename
     * @param string|null $oldSubdir
     * @param string $newSubdir
     * @return void
     *
     * @throws \InstruktoriBrno\TMOU\Services\Files\Exceptions\SourceFileDoesNotExistsException
     * @throws \InstruktoriBrno\TMOU\Services\Files\Exceptions\DestinationFileAlreadyExistsException
     * @throws \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException
     * @throws \InstruktoriBrno\TMOU\Services\Files\Exceptions\FileMoveFailedException
     */
    public function __invoke(string $oldFilename, string $newFilename, ?string $oldSubdir, string $newSubdir): void
    {
        $storageDir = __DIR__ . '/../../../www/storage';
        $srcFilepath = $storageDir . '/' . $oldSubdir . '/' . $oldFilename;
        $destFilepath = $storageDir . '/' . $newSubdir . '/' . $newFilename;
        if (!file_exists($srcFilepath)) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\SourceFileDoesNotExistsException("Source file {$srcFilepath} does not exists.");
        }
        if (file_exists($destFilepath)) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\DestinationFileAlreadyExistsException("Destination file {$destFilepath} already exists.");
        }
        if (realpath($srcFilepath) === false || realpath($storageDir) === false || !str_starts_with(realpath($srcFilepath), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Filename {$srcFilepath} is outside storage dir.");
        }
        if (realpath(dirname($destFilepath)) === false || !str_starts_with(realpath(dirname($destFilepath)), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Filename {$destFilepath} is outside storage dir.");
        }
        try {
            FileSystem::rename($srcFilepath, $destFilepath);
        } catch (\Nette\IOException $exception) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\FileMoveFailedException('Move failed', 0, $exception);
        }
    }
}
