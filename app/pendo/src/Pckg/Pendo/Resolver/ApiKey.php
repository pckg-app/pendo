<?php namespace Pckg\Pendo\Resolver;

use Pckg\Api\Record\AppKey;
use Pckg\Api\Resolver\ApiKey as PckgApiKey;

class ApiKey extends PckgApiKey
{

    protected $header = 'X-Pendo-Api-Key';

    public function resolve($value)
    {
        return AppKey::getOrFail(['key' => $value, 'valid' => true]);
    }

}