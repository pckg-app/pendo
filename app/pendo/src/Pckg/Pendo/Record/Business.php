<?php namespace Pckg\Pendo\Record;

use Pckg\Database\Record;
use Pckg\Pendo\Entity\Businesses;
use Pckg\Pendo\Service\Fiscalization\Business as FiscalizationBusiness;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;

class Business extends Record
{

    protected $entity = Businesses::class;

}