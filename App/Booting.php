<?php declare(strict_types=1);
namespace App;

use Nette\Configurator;

class Booting
{
    public static function boot(): Configurator
    {
        $configurator = new Configurator();

        $configurator->enableTracy(__DIR__ . '/../log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(__DIR__ . '/../temp');

        $configurator->addConfig(__DIR__ . '/Config/common.neon');
        $configurator->addConfig(__DIR__ . '/Config/local.neon');

        return $configurator;
    }
}
