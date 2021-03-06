<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Utils;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

/**
 * Wrapper class for Doctrine DBAL connection to cache serverVersion (as it would be queried on every connection instantiation)
 */
class TunedConnection extends Connection
{
    private const CACHE_FILE_TTL = 3600; // in seconds

    /** @var string */
    private $cacheFilePath = __DIR__ . '/../../temp/cache/MYSQL_VERSION';

    public function __construct(array $params, Driver $driver, ?Configuration $config = null, ?EventManager $eventManager = null)
    {
        if (!isset($params['serverVersion'])) {
            $version = $this->getVersionFromCache();
            if ($version !== null && $version !== '') {
                $params['serverVersion'] = $version;
            }
        }
        parent::__construct($params, $driver, $config, $eventManager);
    }

    private function getVersionFromCache(): ?string
    {
        if (!file_exists($this->cacheFilePath) || @filemtime($this->cacheFilePath) < time() - self::CACHE_FILE_TTL) {
            return null;
        }
        $content = @file_get_contents($this->cacheFilePath);
        if (is_string($content)) {
            return trim($content);
        }
        return null;
    }

    public function __destruct()
    {
        if (!isset($this->getParams()['serverVersion'])) {
            /** @var Driver\Mysqli\MysqliConnection|null $conn */
            $conn = $this->_conn;
            if ($conn !== null) {
                $version = $conn->getServerVersion();
                $this->setVersionToCache($version);
            }
        }
    }

    private function setVersionToCache(string $version): void
    {
        @file_put_contents($this->cacheFilePath, $version, LOCK_EX);
    }
}
