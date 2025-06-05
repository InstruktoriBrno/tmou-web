<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Http\FileUpload;
use function file_exists;
use function realpath;

class UploadToStorageDirectoryService
{
    /**
     * Uploads given file(s) into given subdir
     *
     * @param FileUpload[] $uploads
     * @param bool $overwrite
     * @param string|null $subdir
     * @return array<int, int>
     */
    public function __invoke(array $uploads, bool $overwrite, ?string $subdir): array
    {
        $storageDir = __DIR__ . '/../../../www/storage';
        $subpath = $storageDir . '/' . $subdir;
        if (realpath($subpath) === false || realpath($storageDir) === false || !str_starts_with(realpath($subpath), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Subdir {$subdir} ({$subpath} is outside storage dir.");
        }
        $stored = 0;
        $skipped = 0;
        /** @var FileUpload $upload */
        foreach ($uploads as $upload) {
            $destinationPath = $subpath . '/' . $upload->getName();
            if (file_exists($destinationPath) && !$overwrite) {
                $skipped += 1;
                continue;
            }
            $upload->move($destinationPath);
            $stored += 1;
        }
        return [$stored, $skipped];
    }
}
