<?php namespace Pckg\Pendo\Service\Fiscalization;

class Config
{

    protected $taxNumber;

    protected $pemCert;

    protected $p12Cert;

    protected $password;

    protected $serverCert;

    protected $url;

    protected $softwareSupplierTaxNumber;

    public function __construct(
        $taxNumber,
        $pemCert,
        $p12Cert,
        $password,
        $serverCert,
        $url,
        $softwareSupplierTaxNumber
    ) {
        $this->taxNumber = substr($taxNumber, 2);
        $this->pemCert = $pemCert;
        $this->p12Cert = $p12Cert;
        $this->password = $password;
        $this->serverCert = $serverCert;
        $this->url = $url;
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

    public function getUrl()
    {
        return $this->url;
    }

    public function getSoftwareSupplierTaxNumber()
    {
        return $this->softwareSupplierTaxNumber;
    }

}