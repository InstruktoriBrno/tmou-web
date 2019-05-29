<?php declare(strict_types=1);
namespace App;

use function getenv;
use Nette\Configurator;

class Booting
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();

        // This enables debug mode when accessed (in docker) from "localhost"
        // When using non-standard deployment you may want to add value of $_SERVER['REMOTE_ADDR']
        $allowedDebugAddresses = ['127.0.0.1', '::1', '192.168.99.1'];
        if (((bool) getenv('TRACY_DEBUG_ENABLE')) === true) {
            $allowedDebugAddresses[] = $_SERVER['REMOTE_ADDR'];
        }
        $configurator->setDebugMode($allowedDebugAddresses);
        $configurator->enableTracy(__DIR__ . '/../log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(__DIR__ . '/../temp');

        $configurator->addConfig(__DIR__ . '/Config/common.neon');
        $configurator->addConfig(__DIR__ . '/Config/local.neon');

        return $configurator;
    }
}
