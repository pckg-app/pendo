<?php

use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Auth\Middleware\RegisterApiKeyHeader;
use Pckg\Auth\Provider\Auth;
use Pckg\Framework\Provider;
use Pckg\Framework\Provider\Framework;
use Pckg\Generic\Middleware\EncapsulateResponse;
use Pckg\Generic\Provider\GenericPaths;
use Pckg\Manager\Middleware\RegisterCoreAssets;
use Pckg\Manager\Provider\Manager;
use Pckg\Pendo\Provider\Pendo as PendoProvider;
use Pckg\Pendo\Provider\PendoApi;

class Pendo extends Provider
{

    public function providers()
    {
        return [
            PendoProvider::class,
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
            RegisterApiKeyHeader::class,
            RegisterCoreAssets::class,
        ];
    }

    public function afterwares()
    {
        return [
            EncapsulateResponse::class,
        ];
    }

}