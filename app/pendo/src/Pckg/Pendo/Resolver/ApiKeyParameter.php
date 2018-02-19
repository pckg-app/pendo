<?php namespace Pckg\Pendo\Resolver;

use Pckg\Pendo\Record\AppKey;

class ApiKeyParameter extends ApiKey
{

    public function resolve($value)
    {
        return AppKey::getOrFail(['key' => $value, 'valid' => true]);
    }

}