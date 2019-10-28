<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Utils\Finder;
use Nette\Utils\Strings;
use function iterator_to_array;
use function realpath;

class FindStorageSubdirFilesService
{
    /**
     * Find all files from given subdir of public storage directory
     *
     * @return array
     */
    public function __invoke(?string $subdir): array
    {
        $storageDir = __DIR__ . '/../../../www/storage';
        $subpath = $storageDir . '/' . $subdir;
        if (!Strings::startsWith(realpath($subpath), realpath($storageDir))) {
            throw new \InstruktoriBrno\TMOU\Services\Files\Exceptions\InvalidStorageSubdirException("Subdir ${subdir} (${subpath} is outside storage dir.");
        }
        $files = Finder::findFiles('*')->in($subpath)->getIterator();
        $output = iterator_to_array($files);
        ksort($output, SORT_FLAG_CASE | SORT_STRING);
        return $output;
    }
}
