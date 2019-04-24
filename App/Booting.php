<?php declare(strict_types=1);
namespace App;

use Nette\Configurator;

class Booting
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();

        // This enables debug mode when accessed (in docker) from "localhost"
        // When using non-standard deployment you may want to add value of $_SERVER['REMOTE_ADDR']
        $configurator->setDebugMode(['127.0.0.1', '::1', '192.168.99.1']);
        $configurator->enableTracy(__DIR__ . '/../log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(__DIR__ . '/../temp');

        $configurator->addConfig(__DIR__ . '/Config/common.neon');
        $configurator->addConfig(__DIR__ . '/Config/local.neon');

        return $configurator;
    }
}
