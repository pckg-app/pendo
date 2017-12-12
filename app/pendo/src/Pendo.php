<?php

use Pckg\Framework\Provider;

class Pendo extends Provider
{

    public function routes()
    {
        return [
            routeGroup([
                           'controller' => \Pckg\Pendo\Controller\Pendo::class,
                       ], [
                           'homepage' => route('/', 'index'),
                       ]),
        ];
    }

}