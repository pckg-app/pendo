<?php namespace Pckg\Pendo\Service\Fiscalization\Service;

use Pckg\Pendo\Service\Fiscalization\AbstractService;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Invoice;

class Pendo extends AbstractService
{

    public function createEchoMsg()
    {
        // TODO: Implement createEchoMsg() method.
    }

    public function createBusinessMsg()
    {
        // TODO: Implement createBusinessMsg() method.
    }

    public function createCorrectionMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1)
    {
        // TODO: Implement createCorrectionMsg() method.
    }

    public function createCreditMsg(Business $oldBusiness, Invoice $oldInvoice)
    {
        // TODO: Implement createCreditMsg() method.
    }

    public function createInvoiceMsg()
    {
        // TODO: Implement createInvoiceMsg() method.
    }

    public function createTechnicalCorrectionMsg(
        Business $business,
        Invoice $oldInvoice,
        $correctionNumber = 1
    ) {
        // TODO: Implement createTechnicalCorrectionMsg() method.
    }

    public function createTechnicalStornoMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1)
    {
        // TODO: Implement createTechnicalStornoMsg() method.
    }

    public function signDocument()
    {
        // TODO: Implement signDocument() method.
    }

    public function makeRequest()
    {
        // TODO: Implement makeRequest() method.
    }

    public function envelopeDocument()
    {
        // TODO: Implement envelopeDocument() method.
    }

    public function __construct() { }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

    public function __isset($name)
    {
        // TODO: Implement __isset() method.
    }

    public function __unset($name)
    {
        // TODO: Implement __unset() method.
    }

    public function __sleep()
    {
        // TODO: Implement __sleep() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function __debugInfo()
    {
        // TODO: Implement __debugInfo() method.
    }

    public static function __set_state($an_array)
    {
        // TODO: Implement __set_state() method.
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }


}