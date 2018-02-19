<?php namespace Pckg\Pendo\Service\Fiscalization;

class Config
{

    protected $taxNumber;

    protected $pemCert;

    protected $p12Cert;

    protected $password;

    protected $serverCert;

    protected $softwareSupplierTaxNumber;

    public function __construct(
        $taxNumber,
        $pemCert,
        $p12Cert,
        $password,
        $serverCert,
        $softwareSupplierTaxNumber
    ) {
        $this->taxNumber = substr($taxNumber, 2);
        $this->pemCert = $pemCert;
        $this->p12Cert = $p12Cert;
        $this->password = $password;
        $this->serverCert = $serverCert;
        $this->softwareSupplierTaxNumber = $softwareSupplierTaxNumber;
    }

    public function getTaxNumber()
    {
        return $this->taxNumber;
    }

    public function getPemCert()
    {
        return $this->pemCert;
    }

    public function getP12Cert()
    {
        return $this->p12Cert;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getServerCert()
    {
        return $this->serverCert;
    }

    public function getSoftwareSupplierTaxNumber()
    {
        return $this->softwareSupplierTaxNumber;
    }

    public function getUrl()
    {
        return 'https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest';
        return 'https://blagajne-test.fu.gov.si:9002/v1/cash_registers';
    }

}