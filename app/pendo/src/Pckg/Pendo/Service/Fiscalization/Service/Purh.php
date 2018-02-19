<?php namespace Pckg\Pendo\Service\Fiscalization\Service;

use DateTime;
use DOMDocument;
use DOMElement;
use Exception;
use Pckg\Pendo\Service\Fiscalization\AbstractService;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use XMLWriter;

class Purh extends AbstractService
{

    protected $xmlRequestType; /* RacunZahtjev OR PoslovniProstorZahtjev OR EchoRequest */

    protected $uniqid;

    public function createEchoMsg()
    {
        $this->xmlRequestType = 'EchoRequest';
        $this->uniqid = uniqid();
        $ns = 'tns';
        $writer = new XMLWriter();
        $writer->openMemory();
        //$writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(4);
        $writer->startElementNs($ns, 'EchoRequest', 'http://www.apis-it.hr/fin/2012/types/f73');
        $writer->writeAttribute('Id', $this->uniqid);
        //$writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        //$writer->writeAttribute('xsi:schemaLocation', 'http://www.apis-it.hr/fin/2012/types/f73 FiskalizacijaSchema.xsd');
        $writer->startElementNs($ns, 'Zaglavlje', null);
        $writer->writeElementNs($ns, 'IdPoruke', null, $this->returnUUID());
        $writer->writeElementNs($ns, 'DatumVrijeme', null, date('d.m.Y\TH:i:s'));
        $writer->endElement(); /* #Zaglavlje */

        $writer->endElement(); /* #EchoRequest */
        //$writer->endDocument();
        $this->xmlMessage = $writer->outputMemory();
    }

    public function createBusinessMsg()
    {
        $this->xmlRequestType = 'PoslovniProstorZahtjev';
        $this->uniqid = uniqid();
        $ns = 'tns';
        $writer = new XMLWriter();
        $writer->openMemory();
        //$writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(4);

        $writer->startElementNs($ns, 'PoslovniProstorZahtjev', 'http://www.apis-it.hr/fin/2012/types/f73');
        $writer->writeAttribute('Id', $this->uniqid);
        $writer->startElementNs($ns, 'Zaglavlje', null);
        $writer->writeElementNs($ns, 'IdPoruke', null, $this->returnUUID());
        $writer->writeElementNs($ns, 'DatumVrijeme', null, date('d.m.Y\TH:i:s'));
        $writer->endElement(); /* #Zaglavlje */

        $writer->startElementNs($ns, 'PoslovniProstor', null);
        $writer->writeElementNs($ns, 'Oib', null, $this->business->getTaxNumber());
        $writer->writeElementNs($ns, 'OznPoslProstora', null, $this->business->getId());

        $writer->startElementNs($ns, 'AdresniPodatak', null);
        $writer->startElementNs($ns, 'Adresa', null);
        $writer->writeElementNs($ns, 'Ulica', null, 'Otokara Kersovanija 4');
        $writer->writeElementNs($ns, 'KucniBroj', null, '45');
        $writer->writeElementNs($ns, 'KucniBrojDodatak', null, 'B');
        $writer->writeElementNs($ns, 'BrojPoste', null, '31000');
        $writer->writeElementNs($ns, 'Naselje', null, 'Osijek');
        $writer->writeElementNs($ns, 'Opcina', null, 'Osijek');
        $writer->endElement(); /* #Adresa */
        $writer->endElement(); /* #AdresniPodatak */

        $writer->writeElementNs($ns, 'RadnoVrijeme', null, 'Web stranica');
        $writer->writeElementNs($ns, 'DatumPocetkaPrimjene', null,
                                date('d.m.Y', strtotime($this->business->getValidityDate())));
        $writer->writeElementNs($ns, 'SpecNamj', null, '79343687407'); /* YOUR DEVELOPMENT COMPANY OIB ALWAYS */
        $writer->endElement(); /* #PoslovniProstor */
        $writer->endElement(); /* #PoslovniProstorZahtjev */
        //$writer->endDocument();

        $this->xmlMessage = $writer->outputMemory();

        return;
        $PoslovniProstorZahtjevDOMDocument = new DOMDocument();
        $PoslovniProstorZahtjevDOMDocument->loadXML($writer->outputMemory());
        $PoslovniProstorZahtjevDOMDocument->encoding = 'UTF-8';
        $PoslovniProstorZahtjevDOMDocument->version = '1.0';

        $this->xmlMessage = $PoslovniProstorZahtjevDOMDocument;
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
        $this->xmlRequestType = 'RacunZahtjev';

        $UUID = $this->returnUUID();
        $this->zoi = $this->generateZOI();
        $this->uniqid = uniqid();

        $dt = new DateTime('now');
        $ns = 'tns';
        $writer = new XMLWriter();
        $writer->openMemory();
        //$writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(4);
        $writer->startElementNs($ns, 'RacunZahtjev', 'http://www.apis-it.hr/fin/2012/types/f73');
        $writer->writeAttribute('Id', $this->uniqid);
        $writer->startElementNs($ns, 'Zaglavlje', null);
        $writer->writeElementNs($ns, 'IdPoruke', null, $UUID);
        $writer->writeElementNs($ns, 'DatumVrijeme', null, date('d.m.Y\TH:i:s'));
        $writer->endElement(); // #Zaglavlje
        $writer->startElementNs($ns, 'Racun', null);
        $writer->writeElementNs($ns, 'Oib', null, $this->business->getTaxNumber());
        $writer->writeElementNs($ns, 'USustPdv', null, '1');
        $writer->writeElementNs($ns, 'DatVrijeme', null, $dt->format('d.m.Y\TH:i:s'));
        $writer->writeElementNs($ns, 'OznSlijed', null, 'P'); // P || N
        $writer->startElementNs($ns, 'BrRac', null);
        $writer->writeElementNs($ns, 'BrOznRac', null, $this->invoice->getInvoiceNumber());
        $writer->writeElementNs($ns, 'OznPosPr', null, $this->business->getId());
        $writer->writeElementNs($ns, 'OznNapUr', null, $this->business->getElectronicDeviceId());
        $writer->endElement(); // #BrRac
        $writer->startElementNs($ns, 'Pdv', null);
        $writer->startElementNs($ns, 'Porez', null);
        $writer->writeElementNs($ns, 'Stopa', null, '25.00');
        $writer->writeElementNs($ns, 'Osnovica', null, '100.00');
        $writer->writeElementNs($ns, 'Iznos', null, '25.00');
        $writer->endElement(); // #Porez
        $writer->startElementNs($ns, 'Porez', null);
        $writer->writeElementNs($ns, 'Stopa', null, '5.00');
        $writer->writeElementNs($ns, 'Osnovica', null, '50.00');
        $writer->writeElementNs($ns, 'Iznos', null, '7.50');
        $writer->endElement(); // #Porez
        $writer->endElement(); // #Pdv
        $writer->writeElementNs($ns, 'IznosUkupno', null, $this->invoice->getInvoiceAmount());
        $writer->writeElementNs($ns, 'NacinPlac', null, 'G'); // O || G
        $writer->writeElementNs($ns, 'OibOper', null, $this->business->getTaxNumber());
        $writer->writeElementNs($ns, 'ZastKod', null, $this->zoi);
        $writer->writeElementNs($ns, 'NakDost', null, '0');
        $writer->endElement(); // #Racun

        $writer->endElement(); // #RacunZahtjev
        //$writer->endDocument();
        $this->xmlMessage = $writer->outputMemory();
    }

    public function createTechnicalCorrectionMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1
    ) {
        // TODO: Implement createTechnicalCorrectionMsg() method.
    }

    public function createTechnicalStornoMsg(Business $business, Invoice $oldInvoice, $correctionNumber = 1)
    {
        // TODO: Implement createTechnicalStornoMsg() method.
    }

    public function makeRequest()
    {
        // d('message', $this->xmlMessage);

        $ch = curl_init();
        $options = [
            CURLOPT_URL               => $this->config->getUrl(),
            CURLOPT_CONNECTTIMEOUT_MS => 10000,
            CURLOPT_TIMEOUT_MS        => 10000,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => $this->xmlMessage,
            CURLOPT_SSL_VERIFYHOST    => 2,
            CURLOPT_SSL_VERIFYPEER    => true,
            CURLOPT_CAINFO            => $this->config->getServerCert(),
            CURLOPT_VERBOSE           => dev() ? true : false,
        ];
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        if ($response) {
            //d("response", $response);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $DOMResponse = new DOMDocument();
            $DOMResponse->loadXML($response);
            if ($code === 200) {
                /* For RacunZahtjev */
                $this->zoi = $DOMResponse->getElementsByTagName('Jir')->item(0)->nodeValue;
            } else {
                $SifraGreske = $DOMResponse->getElementsByTagName('SifraGreske')->item(0);
                $PorukaGreske = $DOMResponse->getElementsByTagName('PorukaGreske')->item(0);
                if ($SifraGreske && $PorukaGreske) {
                    throw new Exception(sprintf('%s: %s', $SifraGreske->nodeValue, $PorukaGreske->nodeValue));
                } else {
                    throw new Exception(sprintf('HTTP response code %s not suited for further actions.', $code));
                }
            }
        } else {
            throw new Exception("CURL error: " . curl_error($ch));
        }
        curl_close($ch);
    }

    public function returnUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                       mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                       mt_rand(0, 0xffff),
                       mt_rand(0, 0x0fff) | 0x4000,
                       mt_rand(0, 0x3fff) | 0x8000,
                       mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function generateZOI()
    {
        $dt = new DateTime('now');
        $datumVrijemeIzdavanjaRacuna = $dt->format('d.m.Y H:i:s'); /* use invoice created_at datetime here */
        $ZastKodUnsigned = '';
        $ZastKodUnsigned .= $this->business->getTaxNumber();
        $ZastKodUnsigned .= $datumVrijemeIzdavanjaRacuna;
        $ZastKodUnsigned .= $this->invoice->getInvoiceNumber();
        $ZastKodUnsigned .= $this->business->getId();
        $ZastKodUnsigned .= $this->business->getElectronicDeviceId();
        $ZastKodUnsigned .= $this->invoice->getInvoiceAmount();
        $ZastKodSignature = null;

        openssl_sign($ZastKodUnsigned, $ZastKodSignature, $this->getPrivateKeyResource(), OPENSSL_ALGO_SHA1);

        return md5($ZastKodSignature);
    }

    public function envelopeDocument()
    {
        if (in_array($this->xmlRequestType, ['EchoRequest'])) {

            $XMLRequestDOMDoc = new DOMDocument();
            $XMLRequestDOMDoc->loadXML($this->xmlMessage);
            $this->xmlMessage = $XMLRequestDOMDoc;
        }

        $envelope = new DOMDocument();
        $envelope->loadXML('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    <soapenv:Body></soapenv:Body>
</soapenv:Envelope>');
        $envelope->encoding = 'UTF-8';
        $envelope->version = '1.0';

        $XMLRequestTypeNode = $this->xmlMessage->getElementsByTagName($this->xmlRequestType)->item(0);
        $XMLRequestTypeNode = $envelope->importNode($XMLRequestTypeNode, true);

        $envelope->getElementsByTagName('Body')->item(0)->appendChild($XMLRequestTypeNode);

        /* Final, signed XML request */
        $this->xmlMessage = $envelope->saveXML();
    }

    public function signDocument()
    {
        if (in_array($this->xmlRequestType, ['EchoRequest'])) {
            return;
        }

        $XMLRequestDOMDoc = new DOMDocument();
        $XMLRequestDOMDoc->loadXML($this->xmlMessage);
        $canonical = $XMLRequestDOMDoc->C14N();
        $DigestValue = base64_encode(hash('sha1', $canonical, true));
        $rootElem = $XMLRequestDOMDoc->documentElement;
        $SignatureNode = $rootElem->appendChild(new DOMElement('Signature'));
        $SignatureNode->setAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');
        $SignedInfoNode = $SignatureNode->appendChild(new DOMElement('SignedInfo'));
        $SignedInfoNode->setAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');
        $CanonicalizationMethodNode = $SignedInfoNode->appendChild(new DOMElement('CanonicalizationMethod'));
        $CanonicalizationMethodNode->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
        $SignatureMethodNode = $SignedInfoNode->appendChild(new DOMElement('SignatureMethod'));
        $SignatureMethodNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
        $ReferenceNode = $SignedInfoNode->appendChild(new DOMElement('Reference'));
        $ReferenceNode->setAttribute('URI', sprintf('#%s', $this->uniqid));
        $TransformsNode = $ReferenceNode->appendChild(new DOMElement('Transforms'));
        $Transform1Node = $TransformsNode->appendChild(new DOMElement('Transform'));
        $Transform1Node->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $Transform2Node = $TransformsNode->appendChild(new DOMElement('Transform'));
        $Transform2Node->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');
        $DigestMethodNode = $ReferenceNode->appendChild(new DOMElement('DigestMethod'));
        $DigestMethodNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
        $ReferenceNode->appendChild(new DOMElement('DigestValue', $DigestValue));
        $SignedInfoNode = $XMLRequestDOMDoc->getElementsByTagName('SignedInfo')->item(0);

        $publicCertificateData = $this->getPublicCertificateData();
        $publicCertificate = $this->getRawCertificate()['cert'];

        $publicCertificatePureString = str_replace('-----BEGIN CERTIFICATE-----', '', $publicCertificate);
        $publicCertificatePureString = str_replace('-----END CERTIFICATE-----', '', $publicCertificatePureString);
        $SignedInfoSignature = null;
        if (!openssl_sign($SignedInfoNode->C14N(true), $SignedInfoSignature, $this->getPrivateKeyResource(),
                          OPENSSL_ALGO_SHA1)
        ) {
            throw new Exception('Unable to sign the request');
        }

        $X509Issuer = $publicCertificateData['issuer'];
        $X509IssuerName = sprintf('OU=%s,O=%s,C=%s', $X509Issuer['OU'] ?? null, $X509Issuer['O'], $X509Issuer['C']);
        $X509IssuerSerial = $publicCertificateData['serialNumber'];

        $SignatureNode = $XMLRequestDOMDoc->getElementsByTagName('Signature')->item(0);
        $SignatureValueNode = new DOMElement('SignatureValue', base64_encode($SignedInfoSignature));
        $SignatureNode->appendChild($SignatureValueNode);
        $KeyInfoNode = $SignatureNode->appendChild(new DOMElement('KeyInfo'));
        $X509DataNode = $KeyInfoNode->appendChild(new DOMElement('X509Data'));
        $X509CertificateNode = new DOMElement('X509Certificate', $publicCertificatePureString);
        $X509DataNode->appendChild($X509CertificateNode);
        $X509IssuerSerialNode = $X509DataNode->appendChild(new DOMElement('X509IssuerSerial'));
        $X509IssuerNameNode = new DOMElement('X509IssuerName', $X509IssuerName);
        $X509IssuerSerialNode->appendChild($X509IssuerNameNode);
        $X509SerialNumberNode = new DOMElement('X509SerialNumber', $X509IssuerSerial);
        $X509IssuerSerialNode->appendChild($X509SerialNumberNode);
        $this->xmlMessage = $XMLRequestDOMDoc;
    }

}