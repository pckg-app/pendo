<?php

use Codeception\Configuration;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Pckg\Pendo\Service\Fiscalization\Service\Furs;

class FursTest extends \Codeception\Test\Unit
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $this->tester->initPckg(__DIR__);
    }

    protected function _after()
    {
    }

    /**
     * @return Furs
     */
    protected function createFurs()
    {
        $configuration = Configuration::config()['pckg']['pendo']['furs']['config'] ?? [];

        $config = new Config(
            $configuration['vat_number'],
            path('root') . $configuration['pem'],
            path('root') . $configuration['p12'],
            $configuration['key'],
            path('root') . $configuration['server'],
            '12345678',
            'https://blagajne-test.fu.gov.si:9002/v1/cash_registers'
        );

        $business = new Business($configuration['business'], $configuration['vat_number'],
                                 $configuration['validity_date'],
                                 $configuration['device']);

        $invoice = new Invoice(123, 12323.45, 12323.45, date('Y-m-d') . 'T' . date('H:i:s'));

        $furs = new Furs($config, $business, $invoice);

        return $furs;
    }

    public function testEcho()
    {
        $furs = $this->createFurs();

        $furs->createEchoMsg();
        $furs->postXml();
        $response = $furs->getXmlResponse();

        $this->assertEquals('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:fu="http://www.fu.gov.si/" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><soapenv:Body><fu:EchoResponse>vrni x</fu:EchoResponse>
                </soapenv:Body>
            </soapenv:Envelope>
        ', $response);
    }

    public function testConfirm()
    {
        $furs = $this->createFurs();

        $furs->createInvoiceMsg();
        $furs->postXml();
        $response = $furs->getXmlResponse();
        $zoi = $furs->getZOI();
        $eor = $furs->getEOR();

        $eorRegex = '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}';
        $this->assertRegExp('/[a-z0-9]{32}/s', $zoi);
        $this->assertRegExp('/' . $eorRegex . '/s', $eor);

        $this->assertNotContains('Sporočilo ni v skladu s shemo XML', $response);
        $this->assertContains('<?xml version="1.0" encoding="UTF-8"?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:fu="http://www.fu.gov.si/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><soapenv:Body><fu:InvoiceResponse Id="data"><fu:Header><fu:MessageID>',
                              $response);
        $this->assertContains('</fu:Header><fu:UniqueInvoiceID>', $response);
        $this->assertRegExp('/<fu:UniqueInvoiceID>' . $eorRegex . '<\/fu:UniqueInvoiceID>/s',
                            $response);
    }

}