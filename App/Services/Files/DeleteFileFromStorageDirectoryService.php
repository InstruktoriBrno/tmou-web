<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Utils\FileSystem;
use function is_dir;
use function realpath;

class DeleteFileFromStorageDirectoryService
{
    /**
     * Delete given file in given subdir of storage
     *
     * @param string $filename
     * @param string|null $subdir
     * @return bool
     * @throws \InstruktoriBrno\TMOU\Services\Files\Exceptions\FileDeleteFailedException
     */
    public function __invoke(string $filename, ?string $subdir): bool
    {
        $storageDir = __DIR__ . '/../../../www/storage';
        $filenamePathToRemove = $storageDir . '/' . $subdir . '/' . $filename;
        if (realpath($filenamePathToRemove) === false || realpath($storageDir) === false || !str_starts_with(realpath($filenamePathToRemove), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Filename {$filenamePathToRemove} is outside storage dir.");
        }
        $isDir = is_dir($filenamePathToRemove);
        try {
            FileSystem::delete($filenamePathToRemove);
            return $isDir;
        } catch (\Nette\IOException $exception) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\FileDeleteFailedException('Delete failed', 0, $exception);
        }
    }
}
