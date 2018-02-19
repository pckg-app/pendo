<?php namespace Pckg\Pendo\Resolver;

use Pckg\Api\Resolver\ApiKey as PckgApiKey;

class ApiKey extends PckgApiKey
{

    protected $header = 'X-Pendo-Api-Key';

}