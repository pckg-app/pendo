<?php namespace Pckg\Pendo\Entity;

use Pckg\Pendo\Record\AppKey;

class AppKeys extends \Pckg\Api\Entity\AppKeys
{

    protected $record = AppKey::class;

    public function app()
    {
        return $this->belongsTo(Apps::class)
                    ->foreignKey('app_id');
    }

}