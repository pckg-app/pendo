<?php namespace Pckg\Pendo\Provider;

use Pckg\Framework\Provider;
use Pckg\Pendo\Console\EchoFurs;
use Pckg\Pendo\Console\EchoPurh;
use Pckg\Pendo\Controller\Business;
use Pckg\Pendo\Controller\Company;
use Pckg\Pendo\Controller\Invoice;
use Pckg\Pendo\Controller\Pendo as PendoController;

class Pendo extends Provider
{

    public function routes()
    {
        return [
            /**
             * Presentation routes.
             */
            routeGroup([
                           'controller' => PendoController::class,
                       ], [
                           'homepage'  => route('/', 'index'),
                           'configure' => route('/configure', 'configure'),
                       ]),
            /**
             * API routes.
             */
            routeGroup([
                           'controller' => Company::class,
                           'urlPrefix'  => '/api/company',
                           'namePrefix' => 'api.company',
                       ], [
                           '.register' => route('/register', 'register'),
                       ]),
            routeGroup([
                           'controller' => Business::class,
                           'urlPrefix'  => '/api/business',
                           'namePrefix' => 'api.business',
                       ], [
                           '.register' => route('/register', 'register'),
                       ]),
            routeGroup([
                           'controller' => Invoice::class,
                           'urlPrefix'  => '/api/invoice',
                           'namePrefix' => 'api.invoice',
                       ], [
                           '.confirm' => route('/confirm', 'confirm'),
                       ]),
        ];
    }

    public function consoles()
    {
        return [
            EchoPurh::class,
            EchoFurs::class,
        ];
    }

}