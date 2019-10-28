<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
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
     * @throws \InstruktoriBrno\TMOU\Services\Files\Exceptions\FileDeleteFailedException
     */
    public function __invoke(string $oldFilename, string $newFilename, ?string $oldSubdir, string $newSubdir): void
    {
        $storageDir = __DIR__ . '/../../../www/storage';
        $srcFilepath = $storageDir . '/' . $oldSubdir . '/' . $oldFilename;
        $destFilepath = $storageDir . '/' . $newSubdir . '/' . $newFilename;
        bdump($srcFilepath);
        bdump($destFilepath);
        if (!file_exists($srcFilepath)) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\SourceFileDoesNotExistsException("Source file ${srcFilepath} does not exists.");
        }
        if (file_exists($destFilepath)) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\DestinationFileAlreadyExistsException("Destination file ${destFilepath} already exists.");
        }
        if (!Strings::startsWith(realpath($srcFilepath), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Filename ${srcFilepath} is outside storage dir.");
        }
        if (!Strings::startsWith(realpath(dirname($destFilepath)), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Filename ${destFilepath} is outside storage dir.");
        }
        try {
            FileSystem::rename($srcFilepath, $destFilepath);
        } catch (\Nette\IOException $exception) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\FileDeleteFailedException('Move failed', 0, $exception);
        }
    }
}
