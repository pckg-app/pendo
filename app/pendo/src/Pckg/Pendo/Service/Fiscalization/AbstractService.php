<?php namespace Pckg\Pendo\Service\Fiscalization;

abstract class AbstractService
{

    public $data;

    protected $xmlMessage = '';

    protected $urlPostHeader = [];

    protected $xmlResponse;

    protected $content2SignIdentifier;

    protected $msgIdentifier;

    protected $zoi;

    protected $eor;

    protected $qrDirPath;

    protected $xmlsPath;

    protected $type = null;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Business
     */
    protected $business;

    /**
     * @var Invoice
     */
    protected $invoice;

    public function __construct(Config $config, Business $business, Invoice $invoice = null)
    {
        $this->config = $config;
        $this->business = $business;
        $this->xmlsPath = path('app_private') . 'furs' . path('ds') . 'xml' . path('ds');
        $this->qrDirPath = path('app_private') . 'furs' . path('ds') . 'qr' . path('ds');
        $this->invoice = $invoice;
    }

    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getZOI()
    {
        return $this->zoi;
    }

    public function getEOR()
    {
        return $this->eor;
    }

    public function getXmlResponse()
    {
        return $this->xmlResponse;
    }

    protected function generateXMLMessageFromArray($dom, $dataArray)
    {
        if (empty($dataArray['name'])) {
            return false;
        }

        $element_value = (!empty($dataArray['value'])) ? $dataArray['value'] : null;
        $element = $dom->createElement($dataArray['name'], $element_value);

        if (!empty($dataArray['attributes']) && is_array($dataArray['attributes'])) {
            foreach ($dataArray['attributes'] as $attribute_key => $attribute_value) {
                $element->setAttribute($attribute_key, $attribute_value);
            }
        }

        if (isset($dataArray['children'])) {
            foreach ($dataArray['children'] as $data_key => $child_data) {
                if (!is_numeric($data_key)) {
                    continue;
                }

                $child = $this->generateXMLMessageFromArray($dom, $child_data);
                if ($child) {
                    $element->appendChild($child);
                }
            }
        }

        return $element;
    }

    public abstract function createEchoMsg();

    public abstract function createBusinessMsg();

    public abstract function createCorrectionMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1);

    public abstract function createCreditMsg(Business $oldBusiness, Invoice $oldInvoice);

    public abstract function createInvoiceMsg(array $invoiceData = []);

    public abstract function createTechnicalCorrectionMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1
    );

    public abstract function createTechnicalStornoMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1);

    public abstract function signDocument();

    public abstract function makeRequest();

    public abstract function envelopeDocument();

    public function getRawCertificate()
    {
        $raw = null;
        openssl_pkcs12_read($this->getBinaryCertificate(), $raw, $this->config->getPassword());

        return $raw;
    }

    public function getBinaryCertificate()
    {
        return file_get_contents($this->config->getP12Cert());
    }

    public function getPrivateKeyResource()
    {
        return openssl_pkey_get_private($this->getRawCertificate()['pkey'], $this->config->getPassword());
    }

    public function getPublicCertificateData()
    {
        return openssl_x509_parse($this->getRawCertificate()['cert']);
    }

    public function postXml()
    {
        $this->signDocument();
        $this->envelopeDocument();
        $this->makeRequest();
    }

    protected function saveResponse($doc, $type)
    {
        if (!is_dir($this->xmlsPath)) {
            @mkdir($this->xmlsPath, 0755, true);
        }

        $doc->save($this->getDebugDir($type));
    }

    public function getDebugDir($type)
    {
        return $this->xmlsPath . date('Ymdhis') . '_' . (round(microtime(true) * 1000) % 1000) . '_' . $type . '.xml';
    }

}