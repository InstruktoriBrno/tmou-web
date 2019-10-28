<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Files;

use Nette\Utils\Finder;
use Nette\Utils\Strings;
use SplFileInfo;
use function explode;
use function ksort;

class FindStorageDirectoriesService
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
        $data['/'] = null;
        /** @var SplFileInfo $dir */
        foreach ($all as $dir) {
            $path = Strings::substring($dir->getPathname(), Strings::length($storageDir) + 1);
            // Trick to convert foo/bar/sth to nested array using references
            $parts = explode('/', $path);
            $temp = &$data;
            foreach ($parts as $key) {
                $temp = &$temp[$key];
            }
        }
        $this->recursiveKsort($data);
        return $data;
    }

    private function recursiveKsort(array &$array): bool
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveKsort($value);
            }
        }
        return ksort($array);
    }
}
