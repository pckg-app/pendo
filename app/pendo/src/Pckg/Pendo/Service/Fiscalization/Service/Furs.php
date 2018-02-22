<?php namespace Pckg\Pendo\Service\Fiscalization\Service;

require_once path('root') . 'vendor/robrichards/xmlseclibs/xmlseclibs.php';
// require_once __CORE_OPENPROF_ROOT__ . 'include/library/phpqrcode/phpqrcode.php';

use DOMDocument;
use DOMXPath;
use Pckg\Pendo\Service\Fiscalization\AbstractService;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use PHPQRCode\Constants;
use PHPQRCode\QRcode;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

// official documentation at
// http://www.datoteke.fu.gov.si/dpr/index.html
class Furs extends AbstractService
{

    public function envelopeDocument()
    {
        // TODO: Implement envelopeDocument() method.
    }

    public function createEchoMsg()
    {
        $this->content2SignIdentifier = '';

        $this->urlPostHeader = [
            'Content-Type: text/xml; charset=utf-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: /echo',
        ];
        $dataArray = [
            'name'       => 'soapenv:Envelope',
            'attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:fu'      => 'http://www.fu.gov.si/',
            ],
            'children'   => [
                0 => [
                    'name' => 'soapenv:Header',
                ],
                1 => [
                    'name'     => 'soapenv:Body',
                    'children' => [
                        0 => [
                            'name'  => 'fu:EchoRequest',
                            'value' => 'vrni x',
                        ],
                    ],
                ],
            ],
        ];

        $this->createXMLMessage($dataArray);
    }

    public function createBusinessMsg()
    {
        $this->msgIdentifier = 'data';
        $this->content2SignIdentifier = 'fu:BusinessPremiseRequest';

        $this->urlPostHeader = [
            'Content-Type: text/xml; charset=utf-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: /invoices/register',
        ];

        $headerArray = [
            'name'     => 'fu:Header',
            'children' => [
                0 => [
                    'name'  => 'fu:MessageID',
                    'value' => $this->returnUUID(),
                ],
                1 => [
                    'name'  => 'fu:DateTime',
                    'value' => str_replace(' ', 'T', date('Y-m-d H:i:s')),
                ],
            ],
        ];

        $businessPremiseArray = [
            'name'     => 'fu:BusinessPremise',
            'children' => [
                0 => [
                    'name'  => 'fu:TaxNumber',
                    'value' => $this->config->getTaxNumber(),
                ],
                1 => [
                    'name'  => 'fu:BusinessPremiseID',
                    'value' => $this->business->getId(),
                ],
                2 => [
                    'name'     => 'fu:BPIdentifier',
                    'children' => [
                        0 => [
                            'name'  => 'fu:PremiseType',
                            'value' => 'C',
                        ],
                    ],
                ],
                3 => [
                    'name'  => 'fu:ValidityDate',
                    'value' => $this->business->getValidityDate(),
                ],
                4 => [
                    'name'     => 'fu:SoftwareSupplier',
                    'children' => [
                        0 => [
                            'name'  => 'fu:TaxNumber',
                            'value' => $this->config->getSoftwareSupplierTaxNumber(),
                        ],
                    ],
                ],
            ],
        ];

        $dataArray = [
            'name'       => 'SOAP-ENV:Envelope',
            'attributes' => [
                'xmlns:SOAP-ENV' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:fu'       => 'http://www.fu.gov.si/',
                'xmlns:xd'       => 'http://www.w3.org/2000/09/xmldsig#',
            ],
            'children'   => [
                0 => [
                    'name'     => 'SOAP-ENV:Body',
                    'children' => [
                        0 => [
                            'name'       => 'fu:BusinessPremiseRequest',
                            'attributes' => [
                                'Id' => $this->msgIdentifier,
                            ],
                            'children'   => [
                                0 => $headerArray,
                                1 => $businessPremiseArray,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->createXMLMessage($dataArray);
    }

    public function createCreditMsg(Business $oldBusiness, Invoice $oldInvoice)
    {
        $this->createMsg(
            [
                'name'     => 'fu:ReferenceInvoice',
                'children' => [
                    [
                        'name'     => 'fu:ReferenceInvoiceIdentifier',
                        'children' => [
                            [
                                'name'  => 'fu:BusinessPremiseID',
                                'value' => $oldBusiness->getId(),
                            ],
                            [
                                'name'  => 'fu:ElectronicDeviceID',
                                'value' => $oldBusiness->getElectronicDeviceId(),
                            ],
                            [
                                'name'  => 'fu:InvoiceNumber',
                                'value' => $oldInvoice->getInvoiceNumber(),
                            ],
                        ],
                    ],
                    [
                        'name'  => 'fu:ReferenceInvoiceIssueDateTime',
                        'value' => $oldInvoice->getIssueDateTime(),
                    ],
                ],
            ]
        );
    }

    public function createCorrectionMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1)
    {
        $this->createMsg(
            [
                [
                    'name'     => 'fu:ReferenceInvoice',
                    'children' => [
                        [
                            'name'     => 'fu:ReferenceInvoiceIdentifier',
                            'children' => [
                                [
                                    'name'  => 'fu:BusinessPremiseID',
                                    'value' => $business->getId(),
                                ],
                                [
                                    'name'  => 'fu:ElectronicDeviceID',
                                    'value' => $business->getElectronicDeviceId(),
                                ],
                                [
                                    'name'  => 'fu:InvoiceNumber',
                                    'value' => $oldInvoice->getInvoiceNumber(),
                                ],
                            ],
                        ],
                        [
                            'name'  => 'fu:ReferenceInvoiceIssueDateTime',
                            'value' => $oldInvoice->getIssueDateTime(),
                        ],
                    ],
                ],
                [
                    'name'  => 'fu:SpecialNotes',
                    'value' => 'CHSEQ#' . $correctionNumber . 'DT' . str_replace(' ', 'T', date('Y-m-d H:i:s')) . ';',
                ],
            ],
            $oldInvoice->getIssueDateTime()
        );
    }

    public function createTechnicalCorrectionMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1)
    {
        $this->createMsg(
            [
                [
                    'name'     => 'fu:ReferenceInvoice',
                    'children' => [
                        [
                            'name'     => 'fu:ReferenceInvoiceIdentifier',
                            'children' => [
                                [
                                    'name'  => 'fu:BusinessPremiseID',
                                    'value' => $business->getId(),
                                ],
                                [
                                    'name'  => 'fu:ElectronicDeviceID',
                                    'value' => $business->getElectronicDeviceId(),
                                ],
                                [
                                    'name'  => 'fu:InvoiceNumber',
                                    'value' => $oldInvoice->getInvoiceNumber(),
                                ],
                            ],
                        ],
                        [
                            'name'  => 'fu:ReferenceInvoiceIssueDateTime',
                            'value' => $oldInvoice->getIssueDateTime(),
                        ],
                    ],
                ],
                [
                    'name'  => 'fu:SpecialNotes',
                    'value' => 'T-CHSEQ#' . $correctionNumber . 'DT' . str_replace(' ', 'T', date('Y-m-d H:i:s')) . ';',
                ],
            ],
            $oldInvoice->getIssueDateTime()
        );
    }

    public function createTechnicalStornoMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1)
    {
        $this->createMsg(
            [
                [
                    'name'     => 'fu:ReferenceInvoice',
                    'children' => [
                        [
                            'name'     => 'fu:ReferenceInvoiceIdentifier',
                            'children' => [
                                [
                                    'name'  => 'fu:BusinessPremiseID',
                                    'value' => $business->getId(),
                                ],
                                [
                                    'name'  => 'fu:ElectronicDeviceID',
                                    'value' => $business->getElectronicDeviceId(),
                                ],
                                [
                                    'name'  => 'fu:InvoiceNumber',
                                    'value' => $oldInvoice->getInvoiceNumber(),
                                ],
                            ],
                        ],
                        [
                            'name'  => 'fu:ReferenceInvoiceIssueDateTime',
                            'value' => $oldInvoice->getIssueDateTime(),
                        ],
                    ],
                ],
                [
                    'name'  => 'fu:SpecialNotes',
                    'value' => 'S-CHSEQ#' . $correctionNumber . 'DT' . str_replace(' ', 'T', date('Y-m-d H:i:s')) . '',
                ],
            ],
            $oldInvoice->getIssueDateTime()
        );
    }

    public function createInvoiceMsg()
    {
        $this->createMsg();
    }

    public function createMsg($subsequentSubmitArray = [], $datetime = null)
    {
        $messageID = $this->returnUUID();
        $dateTime = str_replace(' ', 'T', $datetime ? $datetime : date('Y-m-d H:i:s'));

        $this->zoi = $this->generateZOI();

        $this->msgIdentifier = $this->invoice->getInvoiceNumber();
        $this->content2SignIdentifier = 'fu:InvoiceRequest';

        $this->urlPostHeader = [
            'Content-Type: text/xml; charset=utf-8',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: /invoices',
        ];

        $headerInvoice = [
            'name'     => 'fu:Header',
            'children' => [
                0 => [
                    'name'  => 'fu:MessageID',
                    'value' => $messageID,
                ],
                1 => [
                    'name'  => 'fu:DateTime',
                    'value' => $dateTime,
                ],
            ],
        ];
        $invoiceAmount = $this->invoice->getInvoiceAmount();
        $paymentAmount = $this->invoice->getPaymentAmount();
        if ($invoiceAmount == 0) {
            $invoiceAmount = '0.00';
            $paymentAmount = '0.00';
        }
        $bodyInvoice = [
            'name'     => 'fu:Invoice',
            'children' => [
                [
                    'name'  => 'fu:TaxNumber',
                    'value' => $this->business->getTaxNumber(),
                ],
                [
                    'name'  => 'fu:IssueDateTime',
                    'value' => $this->invoice->getIssueDateTime(),
                ],
                [
                    'name'  => 'fu:NumberingStructure',
                    'value' => 'B',
                ],
                [
                    'name'     => 'fu:InvoiceIdentifier',
                    'children' => [
                        [
                            'name'  => 'fu:BusinessPremiseID',
                            'value' => $this->business->getId(),
                        ],
                        [
                            'name'  => 'fu:ElectronicDeviceID',
                            'value' => $this->business->getElectronicDeviceId(),
                        ],
                        [
                            'name'  => 'fu:InvoiceNumber',
                            'value' => $this->invoice->getInvoiceNumber(),
                        ],
                    ],
                ],
                [
                    'name'  => 'fu:InvoiceAmount',
                    'value' => $invoiceAmount,
                ],
                [
                    'name'  => 'fu:PaymentAmount',
                    'value' => $paymentAmount,
                ],
                [
                    'name' => 'fu:TaxesPerSeller',
                    /*'children' => [
                        0 => [
                            'name'   => 'fu:VAT',
                            'children' => [
                                0 => [
                                    'name'  => 'fu:TaxRate',
                                    'value' => '22.0',
                                ],
                                1 => [
                                    'name'  => 'fu:TaxableAmount',
                                    'value' => '0',
                                ],
                                2 => [
                                    'name'  => 'fu:TaxAmount',
                                    'value' => '0',
                                ],
                            ],
                        ],
                    ],*/
                ],
                [
                    'name'  => 'fu:OperatorTaxNumber',
                    'value' => $this->business->getTaxNumber(),
                ],
                [
                    'name'  => 'fu:ProtectedID',
                    'value' => $this->zoi,
                ],
            ],
        ];

        if (isset($subsequentSubmitArray[0])) {
            foreach ($subsequentSubmitArray as $arr) {
                $bodyInvoice['children'][] = $arr;
            }
        } else {
            $bodyInvoice['children'][] = $subsequentSubmitArray;
        }

        $headerArray = [
            'name' => 'soapenv:Header',
        ];
        $headerBody = [
            'name'     => 'soapenv:Body',
            'children' => [
                [
                    'name'       => 'fu:InvoiceRequest',
                    'attributes' => [
                        'Id' => $this->msgIdentifier,
                    ],
                    'children'   => [
                        0 => $headerInvoice,
                        1 => $bodyInvoice,
                    ],
                ],
            ],
        ];

        $dataArray = [
            'name'       => 'soapenv:Envelope',
            'attributes' => [
                'xmlns:soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'xmlns:fu'      => 'http://www.fu.gov.si/',
                'xmlns:xd'      => 'http://www.w3.org/2000/09/xmldsig#',
                'xmlns:xsi'     => 'http://www.w3.org/2001/XMLSchema-instance',
            ],
            'children'   => [
                0 => $headerArray,
                1 => $headerBody,
            ],
        ];

        $this->createXMLMessage($dataArray);
    }

    private function createXMLMessage($dataArray)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $child = $this->generateXMLMessageFromArray($dom, $dataArray);
        if ($child) {
            $dom->appendChild($child);
        }
        $dom->formatOutput = true;
        $this->xmlMessage = $dom->saveXML();
    }

    public function generateZOI()
    {
        /**
         * IssueDateTime in xml scheme is    YYYY-MM-DDTHH:MM:SS
         * IssueDateTime in zoi is           DD.MM.YYYY HH:MM:SS
         */

        $businessPremiseID = $this->business->getId();
        $electronicDeviceID = $this->business->getElectronicDeviceId();
        $newIssueDateTime = date("d.m.Y H:i:s", strtotime($this->invoice->getIssueDateTime()));
        $signData = $this->config->getTaxNumber() . $newIssueDateTime . $this->invoice->getInvoiceNumber() .
                    $businessPremiseID . $electronicDeviceID . $this->invoice->getInvoiceAmount();

        $key = openssl_pkey_get_private('file://' . $this->config->getPemCert(), $this->config->getPassword());
        openssl_sign($signData, $signature, $key, OPENSSL_ALGO_SHA256);
        openssl_free_key($key);

        return md5($signature);
    }

    public function returnUUID()
    {
        $data = openssl_random_pseudo_bytes(16);
        // in case of PHP 7 use random_bytes
        $data = random_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function returnUUID2()
    {
        mt_srand(crc32(serialize(microtime(true))));

        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public function signDocument()
    {
        if (!$this->content2SignIdentifier) {
            return;
        }
        // get content to sign
        // get content to sign

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($this->xmlMessage);
        $xpath = new DOMXPath($doc);
        $nodeset = $xpath->query("//$this->content2SignIdentifier")->item(0);

        $objXMLSecDSig = new XMLSecurityDSig('');
        $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objXMLSecDSig->addReference(
            $nodeset,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['id_name' => 'Id', 'uri' => $this->msgIdentifier, 'overwrite' => false]
        );

        $raw = $this->getRawCertificate();

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $objKey->loadKey($raw['pkey']);
        $objKey->passphrase = $this->config->getPassword();
        $objXMLSecDSig->sign($objKey, $nodeset);
        $objXMLSecDSig->add509Cert(
            $raw['cert'],
            true,
            false,
            ['issuerSerial' => true, 'subjectName' => true, 'issuerCertificate' => false]
        );

        $objXMLSecDSig->appendSignature($nodeset);
        $this->saveResponse($doc, 'signed');

        $this->xmlMessage = $doc->saveXML();
    }

    public function makeRequest()
    {
        try {
            $conn = curl_init();
            $settings = [
                CURLOPT_URL               => $this->config->getUrl(),
                CURLOPT_FRESH_CONNECT     => true,
                CURLOPT_CONNECTTIMEOUT_MS => 10000,
                CURLOPT_TIMEOUT_MS        => 10000,
                CURLOPT_RETURNTRANSFER    => true,
                CURLOPT_POST              => 1,
                CURLOPT_HTTPHEADER        => $this->urlPostHeader,
                CURLOPT_POSTFIELDS        => $this->xmlMessage,
                CURLOPT_SSL_VERIFYHOST    => 2,
                CURLOPT_SSL_VERIFYPEER    => true,
                CURLOPT_SSLCERT           => $this->config->getPemCert(),
                CURLOPT_SSLCERTPASSWD     => $this->config->getPassword(),
                CURLOPT_CAINFO            => $this->config->getServerCert(),
                CURLOPT_VERBOSE           => false,
                //dev() ? true : false,
                //CURLOPT_SSLVERSION => 4
                //CURLOPT_CAINFO => 'cert/furs_server.pem', //prevejanje server certifikata - uporabi: openssl x509 -inform der -in sitest-ca.cer -out furs_server.pem
                //CURLOPT_SSLCERT => 'cert/furs_client.pem', //dodas svoj certifikat - uporabi: openssl pkcs12 -in ****.p12 -out furs_client.pem -password pass:*****
            ];
            //d("Request", $this->xmlMessage);
            curl_setopt_array($conn, $settings);
            $this->xmlResponse = curl_exec($conn);
            if ($this->xmlResponse) {
                $doc = new DOMDocument('1.0', 'UTF-8');
                $doc->loadXML($this->xmlResponse);
                $this->saveResponse($doc, 'generated');
                if (isset($this->invoice)) {
                    $xpath = new DOMXPath($doc);
                    $nodeset = $xpath->query("//fu:UniqueInvoiceID")->item(0);
                    $this->eor = $nodeset->nodeValue ?? null;
                }
            } else {
                var_dump(curl_error($conn));
            }
            curl_close($conn);
        } catch (\Throwable $e) {
            dd(exception($e));
        }
    }

    public function getEcho()
    {
        if ($this->xmlResponse) {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->loadXML($this->xmlResponse);
            $this->saveResponse($doc, 'generated');

            $xpath = new DOMXPath($doc);
            $nodeset = $xpath->query("//fu:EchoResponse")->item(0);

            return $nodeset->nodeValue ?? null;
        }
    }

    protected function saveResponse($doc, $type)
    {
        if (!is_dir($this->xmlsPath)) {
            @mkdir($this->xmlsPath, 0755, true);
        }

        $doc->save(
            $this->xmlsPath . date('Ymdhis') . '_' . substr(sha1($this->msgIdentifier), 0, 6) . '_' . $type . '.xml'
        );
    }

    private function md52dec($hex)
    {
        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }

        return $dec;
    }

    public function generateQR()
    {
        if (!isset($this->invoice)) {
            return;
        }

        // QR code is made of:
        // 39 chars of decimal ZOI code
        // 8  chars of company's tax num
        // 12 chars of invoice's date & time
        // 1  char is a control number
        // ZOI decimal number
        // ZOI decimal number

        $zoiDecimal = $this->md52dec($this->zoi);

        $zeros2Add = 39 - strlen($zoiDecimal);
        for ($i = 0; $i < $zeros2Add; $i++) {
            $zoiDecimal = '0' . $zoiDecimal;
        }

        $tmpNum = explode('T', $this->invoice->getIssueDateTime());
        $tmpDate = explode('-', $tmpNum[0]);

        $dateTimeNumber = substr($tmpDate[0], 2);
        $dateTimeNumber .= $tmpDate[1];
        $dateTimeNumber .= $tmpDate[2];
        $dateTimeNumber .= $tmpNum[1];
        $dateTimeNumber = str_replace(':', '', $dateTimeNumber);

        $qrCode = $zoiDecimal . $this->config->getTaxNumber() . $dateTimeNumber;
        $controlChar = array_sum(str_split($qrCode)) % 10;

        $qrCode = $qrCode . $controlChar;
        $file = $this->invoice->getInvoiceNumber() . '-' . date('Ymdhis') . '.png';
        QRcode::png(
            $qrCode,
            $this->qrDirPath . $file,
            Constants::QR_ECLEVEL_L,
            6
        );

        return $file;
    }

}
