<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return static function (RouteBuilder $routes) {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder) {
        /*
         * Here, we are connecting '/' (base path) to a controller.
         * You have 3 options of where the base path goes.
         *   1) Pages are public facing
         *   2) Contents requires $this->Auth()
         *   3) Prefix redirection - ultimately will redirect to a Dashboard based on the User's Role
         * You must manually un-comment 1 of the following 3 lines
         */
        //$builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
        //$builder->connect('/', ['controller' => 'Contents', 'action' => 'display', 'home']);
        $builder->connect('/', ['controller' => 'PrefixRedirections', 'action' => 'index']);

        /*
         * Route for Pages static pages (public facing)
         */
        $builder->connect('/pages/*', 'Pages::display');

        /*
         * Route for Contents static pages (internal only i.e. Auth required)
         */
        $builder->connect('/contents/*', 'Contents::display');

        /**
         * ...and catch base prefix routes.
         */
        $builder->connect('/administrators', ['controller' => 'PrefixRedirections', 'action' => 'index']);
        $builder->connect('/producers', ['controller' => 'PrefixRedirections', 'action' => 'index']);
        $builder->connect('/consumers', ['controller' => 'PrefixRedirections', 'action' => 'index']);

        /**
         * ...and connect pretty URLs.
         */
        $builder->connect('/bcn/*', ['controller' => 'Messages', 'action' => 'beacons']);

        /**
         * ...and User Hub pretty URLs.
         * primer, login, logout, reset, forgot, confirm, request, approve, deny
         */
        $builder->connect('/primer/*', ['controller' => 'UserHub', 'action' => 'primer']);
        $builder->connect('/login/*', ['controller' => 'UserHub', 'action' => 'login']);
        $builder->connect('/logout/*', ['controller' => 'UserHub', 'action' => 'logout']);
        $builder->connect('/reset/*', ['controller' => 'UserHub', 'action' => 'reset']);
        $builder->connect('/forgot/*', ['controller' => 'UserHub', 'action' => 'forgot']);
        $builder->connect('/confirm/*', ['controller' => 'UserHub', 'action' => 'confirm']);
        $builder->connect('/request/*', ['controller' => 'UserHub', 'action' => 'request']);
        $builder->connect('/approve/*', ['controller' => 'UserHub', 'action' => 'approve']);
        $builder->connect('/deny/*', ['controller' => 'UserHub', 'action' => 'deny']);
        $builder->connect('/subscription/*', ['controller' => 'UserHub', 'action' => 'subscription']);

        /**
         * ...and ConnectorWebHotFolders Controller pretty URLs.
         */
        $builder->connect('/hot-folders/{action}/*', ['controller' => 'ConnectorWebHotFolders']);

        /**
         * ...and ConnectorArtifacts Controller pretty URLs.
         */
        $builder->connect('/artifacts-connector/{action}/*', ['controller' => 'ConnectorArtifacts']);

        /**
         * ...and ConnectorOpen Controller pretty URLs.
         */
        $builder->connect('/data-receiver/{action}/*', ['controller' => 'ConnectorDataBlobs']);

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * You can remove these routes once you've connected the
         * routes you want in your application.
         */
        $builder->fallbacks();
    });

    $routes->prefix('Administrators', function (RouteBuilder $routes) {

        // All routes here will be prefixed with `/administrators`, and
        // have the `'prefix' => 'Administrators'` route element added that
        // will be required when generating URLs for these routes
        $routes->fallbacks(DashedRoute::class);
    });

    $routes->prefix('Producers', function (RouteBuilder $routes) {

        // All routes here will be prefixed with `/producers`, and
        // have the `'prefix' => 'Producers'` route element added that
        // will be required when generating URLs for these routes
        $routes->fallbacks(DashedRoute::class);
    });

    $routes->prefix('Consumers', function (RouteBuilder $routes) {

        // All routes here will be prefixed with `/consumers`, and
        // have the `'prefix' => 'Consumers'` route element added that
        // will be required when generating URLs for these routes
        $routes->fallbacks(DashedRoute::class);
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder) {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
