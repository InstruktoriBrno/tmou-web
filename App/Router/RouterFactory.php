<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Router;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\StaticClass;

final class RouterFactory
{
    use StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList();

        // Pages (reserved and with slug)
        $router[] = new Route('[<eventNumber \d+>/]page/<slug>', 'Pages:show');
        $router[] = new Route('[<eventNumber \d+>/]page/<action>', 'Pages:show');

        // Administration of events
        $router[] = new Route('admin/events/<action>', 'Events:default');

        // Administration of pages
        $router[] = new Route('admin/pages/[<eventNumber \d+>/][<action>]', 'AdminPages:default');

        // Administration of pages
        $router[] = new Route('admin/teams/[<eventNumber \d+>/][<action>]', 'Teams:default');

        // Administration of users (login, logout, organizators...)
        $router[] = new Route('admin/<action>', 'Admin:default');

        // Default route
        $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }
}
