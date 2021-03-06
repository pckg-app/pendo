<?php namespace Pckg\Pendo\Entity;

use Pckg\Database\Entity;
use Pckg\Pendo\Record\Company;

class Companies extends Entity
{

    protected $record = Company::class;

    public function country()
    {
        return $this->belongsTo(Countries::class)
                    ->foreignKey('country_id');
    }

}