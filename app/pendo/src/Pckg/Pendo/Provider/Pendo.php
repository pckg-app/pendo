<?php namespace Pckg\Pendo\Provider;

use Pckg\Framework\Provider;
use Pckg\Pendo\Console\EchoFurs;
use Pckg\Pendo\Console\EchoPurh;
use Pckg\Pendo\Controller\Business;
use Pckg\Pendo\Controller\Company;
use Pckg\Pendo\Controller\Invoice;
use Pckg\Pendo\Controller\Pendo as PendoController;
use Pckg\Pendo\Resolver\ApiKey;
use Pckg\Pendo\Resolver\ApiKeyParameter;

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
                           'homepage'       => route('/', 'index'),
                           'configureEmpty' => route('/configure', 'configure'),
                           'configure'      => route('/configure/[apiKey]', 'configure')->resolvers([
                                                                                                        'apiKey' => ApiKeyParameter::class,
                                                                                                    ]),
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
            routeGroup([
                           'controller' => PendoController::class,
                           'urlPrefix'  => '/api/fiscalizations',
                           'namePrefix' => 'api.fiscalizations',
                       ], [
                           '' => route('', 'fiscalizations')->resolvers([ApiKey::class]),
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