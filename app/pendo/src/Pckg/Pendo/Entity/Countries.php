<?php namespace Pckg\Pendo\Entity;

use Pckg\Database\Entity;
use Pckg\Pendo\Record\Country;

class Countries extends Entity
{

    protected $record = Country::class;

}