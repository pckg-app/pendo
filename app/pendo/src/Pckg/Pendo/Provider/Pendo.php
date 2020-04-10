<?php namespace Pckg\Pendo\Provider;

use Pckg\Framework\Provider;
use Pckg\Pendo\Console\DebugCert;
use Pckg\Pendo\Console\EchoFurs;
use Pckg\Pendo\Console\EchoPurh;
use Pckg\Pendo\Console\InvoicePurh;
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
                       ],
                       [
                           'homepage'       => route('/', 'index'),
                           'configureEmpty' => route('/configure', 'configure'),
                           'configure'      => route('/configure/[apiKey]', 'configure')->resolvers([
                               'apiKey' => ApiKeyParameter::class,
                           ]),
                           'certificate'      => route('/configure/[apiKey]/certificate', 'uploadCertificate')->resolvers([
                               'apiKey' => ApiKeyParameter::class,
                           ]),
                           'validateCertificate'      => route('/configure/[apiKey]/validate-certificate', 'validateCertificate')->resolvers([
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
                       ],
                       [
                           '.register' => route('/register', 'register'),
                       ]),
            routeGroup([
                           'controller' => Business::class,
                           'urlPrefix'  => '/api/business',
                           'namePrefix' => 'api.business',
                       ],
                       [
                           '.register' => route('/register', 'register'),
                       ]),
            routeGroup([
                           'controller' => Invoice::class,
                           'urlPrefix'  => '/api/invoice',
                           'namePrefix' => 'api.invoice',
                           'tags'       => ['auth:in'],
                       ],
                       [
                           '.confirm' => route('/confirm', 'confirm')->resolvers([ApiKey::class]),
                       ]),
            routeGroup([
                           'controller' => PendoController::class,
                           'urlPrefix'  => '/api/fiscalizations',
                           'namePrefix' => 'api.fiscalizations',
                           'tags'       => ['auth:in'],
                       ],
                       [
                           '' => route('', 'fiscalizations')->resolvers([ApiKey::class]),
                       ]),
            routeGroup([
                           'controller' => PendoController::class,
                           'urlPrefix'  => '/check',
                           'namePrefix' => 'check',
                       ],
                       [
                           '' => route('', 'check'),
                       ]),
        ];
    }

    public function consoles()
    {
        return [
            EchoPurh::class,
            InvoicePurh::class,
            EchoFurs::class,
            DebugCert::class,
        ];
    }

}