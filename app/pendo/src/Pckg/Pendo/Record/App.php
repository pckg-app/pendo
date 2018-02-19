<?php namespace Pckg\Pendo\Record;

use Pckg\Database\Record;
use Pckg\Pendo\Entity\Apps;

/**
 * Class App
 *
 * @package Pckg\Pendo\Record
 * @property Company company
 */
class App extends Record
{

    protected $entity = Apps::class;

}