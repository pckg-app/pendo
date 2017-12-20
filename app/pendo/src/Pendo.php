<?php

use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Auth\Provider\Auth;
use Pckg\Framework\Provider;
use Pckg\Generic\Provider\GenericPaths;
use Pckg\Pendo\Provider\Pendo as PendoProvider;

class Pendo extends Provider
{

    public function providers()
    {
        return [
            PendoProvider::class,
            //Auth::class,
            GenericPaths::class,
        ];
    }

    public function middlewares()
    {
        return [
            // LoginWithApiKeyHeader::class,
        ];
    }

}