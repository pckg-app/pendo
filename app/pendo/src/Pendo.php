<?php

use Pckg\Framework\Provider;
use Pckg\Pendo\Provider\Pendo as PendoProvider;

class Pendo extends Provider
{

    public function providers()
    {
        return [
            PendoProvider::class,
        ];
    }

}