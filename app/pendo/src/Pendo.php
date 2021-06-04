<?php

use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Auth\Provider\Auth;
use Pckg\Framework\Provider;
use Pckg\Framework\Provider\Framework;
use Pckg\Generic\Middleware\EncapsulateResponse;
use Pckg\Generic\Provider\GenericPaths;
use Pckg\Manager\Provider\Manager;
use Pckg\Pendo\Provider\Pendo as PendoProvider;
use Pckg\Pendo\Provider\PendoApi;

class Pendo extends Provider
{

    public function providers()
    {
        return [
            PendoProvider::class,
            Provider\Frontend::class,
            PendoApi::class,
            Auth::class,
            Framework::class,
            GenericPaths::class,
            Manager::class,
        ];
    }

    public function middlewares()
    {
        return [
            \Pckg\Pendo\Middleware\RegisterApiKeyHeader::class, // moved from pckg/auth
        ];
    }

    public function afterwares()
    {
        return [
            EncapsulateResponse::class,
        ];
    }

    public function assets() {
        return [
            'libraries' => [
                '/build/libraries.js',
                //'/build/js/app.css',
                '/node_modules/bootstrap/dist/css/bootstrap.min.css',
                'less/app.css',
            ],
            'footer' => [
                '/build/app.js'
            ],
        ];
    }

}
