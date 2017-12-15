<?php

use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Auth\Provider\Auth;
use Pckg\Framework\Provider;
use Pckg\Pendo\Provider\Pendo as PendoProvider;

class Pendo extends Provider
{

    public function providers()
    {
        return [
            PendoProvider::class,
            Auth::class,
        ];
    }

    public function middlewares()
    {
        return [
            LoginWithApiKeyHeader::class,
        ];
    }

}