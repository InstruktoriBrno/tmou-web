<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Utils\Finder;
use Nette\Utils\Strings;
use SplFileInfo;
use function ksort;

class FindStorageDirectoriesPairsService
{
    /**
     * Find all directories (as tree) in public storage directory
     *
     * @return array
     */
    public function __invoke(): array
    {
        $storageDir = __DIR__ . '/../../../www/storage';
        $all = Finder::findDirectories('*')->from($storageDir);

        $data = [];
        $data['/'] = '/';
        /** @var SplFileInfo $dir */
        foreach ($all as $dir) {
            $path = Strings::substring($dir->getPathname(), Strings::length($storageDir) + 1);
            $data[$path] = $path;
        }
        ksort($data, SORT_FLAG_CASE | SORT_NATURAL);
        return $data;
    }
}
