<?php

use Codeception\Configuration;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Pckg\Pendo\Service\Fiscalization\Service\Purh;

class PurhTest extends \Codeception\Test\Unit
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
     * @return Purh
     */
    protected function createPurh()
    {
        /*
        Certifikati koje koristi CIS pristupna točka Porezne uprave po okolinama imaju sljedeće nazive:

        1. Produkcijska
        • Poslužiteljski: cis.porezna-uprava.hr
        • Aplikacijski: fiskalcis

        2. Testna
        • Poslužiteljski: cistest.apis-i
        • Aplikacijski: fiskalcistest

        http://www.fina.hr/Default.aspx?sec=1799.
        http://www.fina.hr/finadigicert

        1. TEST
        Okolina: TEST
        Servis: Prihvat podataka o računima (FiskalizacijaServiceTest)
        URL: https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest

        2. PRODUKCIJA
        Okolina: PRODUKCIJA
        Servis: Prihvat podataka o računima (FiskalizacijaService)
        URL: https://cis.porezna-uprava.hr:8449/FiskalizacijaService
        */
        $configuration = Configuration::config()['pckg']['pendo']['purh']['config'] ?? [];

        $config = new Config(
            $configuration['vat_number'],
            path('root') . $configuration['pem'],
            path('root') . $configuration['p12'],
            $configuration['key'],
            path('root') . $configuration['server'],
            '12345678',
            'https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest'
        );

        $business = new Business($configuration['business'], $configuration['vat_number'],
                                 $configuration['validity_date'],
                                 $configuration['device']);

        $invoice = new Invoice(123, 12323.45, 12323.45, date('Y-m-d') . 'T' . date('H:i:s'));

        $purh = new Purh($config, $business, $invoice);

        return $purh;
    }

    public function testEcho()
    {
        $purh = $this->createPurh();

        $purh->createEchoMsg();
        $purh->postXml();
        $response = $purh->getXmlResponse();

        $this->assertStringStartsWith('
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<tns:EchoResponse xsi:schemaLocation="http://www.apis-it.hr/fin/2012/types/f73 FiskalizacijaSchema.xsd " xmlns:tns="http://www.apis-it.hr/fin/2012/types/f73">',
                                      $response);
        $this->assertRegExp('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}(.*)[0-9]{1,2}.[0-9]{1,2}.[0-9]{4}T[0-9]{2}:[0-9]{2}:[0-9]{2}/s',
                            $response);
    }

    public function testConfirm()
    {
        $purh = $this->createPurh();

        $purh->createInvoiceMsg();
        $purh->postXml();
        $response = $purh->getXmlResponse();
        $zoi = $purh->getZOI();
        $eor = $purh->getEOR();

        $eorRegex = '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}';
        $this->assertRegExp('/[a-z0-9]{32}/s', $zoi);
        $this->assertRegExp('/' . $eorRegex . '/s', $eor);

        $this->assertNotContains('Poruka nije u skladu s XML shemom', $response);
        $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:regexp="http://exslt.org/regular-expressions"><soap:Body><tns:RacunOdgovor Id="',
                              $response);
        $this->assertContains('</tns:Zaglavlje><tns:Jir>', $response);
        $this->assertRegExp('/<tns:Jir>' . $eorRegex . '<\/tns:Jir>/s',
                            $response);
    }

}