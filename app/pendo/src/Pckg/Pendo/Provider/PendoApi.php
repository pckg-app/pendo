<?php namespace Pckg\Pendo\Provider;

use Pckg\Auth\Middleware\LoginWithApiKeyHeader;
use Pckg\Framework\Provider;

class PendoApi extends Provider
{

    public function middlewares()
    {
        return [
            LoginWithApiKeyHeader::class,
        ];

    }

}