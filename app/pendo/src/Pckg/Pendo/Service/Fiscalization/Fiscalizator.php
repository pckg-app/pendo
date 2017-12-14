<?php namespace Pckg\Pendo\Service\Fiscalization;

use Pckg\Pendo\Entity\Fiscalizations;
use Pckg\Pendo\Service\Fiscalization\Service\Furs;
use Pckg\Pendo\Service\Fiscalization\Service\Purh;

class Fiscalizator
{

    protected $order;

    protected $price;

    protected $platformId;

    protected $saveZoi;

    /**
     * @var \Pckg\Pendo\Record\Fiscalization
     */
    protected $fiscalizationRecord;

    protected $fiscalizationBusiness;

    protected $invoiceData;

    public function __construct(Config $config, Business $business, array $invoiceData)
    {
        $this->config = $config;
        $this->fiscalizationBusiness = $business;
        $this->invoiceData = $invoiceData;
    }

    public function createInvoice()
    {
        return new Invoice(
            $this->fiscalizationRecord->furs_id,
            number_format($this->price, 2),
            number_format($this->price, 2),
            date('Y-m-d') . 'T' . date('H:i:s')
        );
    }

    /**
     * @return Furs|Purh
     */
    public function createServiceFromBusiness()
    {

        $taxNumber = $this->fiscalizationBusiness->getTaxNumber();

        $code = strtolower(substr($taxNumber, 2));

        if ($code == 'si') {
            return new Furs($this->config, $this->fiscalizationBusiness);
        } else {
            return new Purh($this->config, $this->fiscalizationBusiness);
        }
    }

    public function fiscalize()
    {
        /**
         * Create fiscalization business and service service based on company country (furs or purh).
         */
        $fiscalizationService = $this->createServiceFromBusiness();

        /**
         * We're fiscalizing some bill, so we need to set some data.
         */
        $fiscalizationService->setOrder(only($this->invoiceData, ['payment', 'datetime', 'identifier', 'platform']));

        /**
         * Create new fiscalization record for fiscalization business.
         */
        $this->createFiscalizationRecord();

        /**
         * Create invoice request.
         */
        $fiscalizationService->createInvoiceMsg();
        $fiscalizationService->postXml();

        /**
         * Generate QR code.
         */
        if ($qrFile = $fiscalizationService->generateQR()) {
            $this->fiscalizationRecord->qr = $qrFile;
        }

        /**
         * Set EOR code, which is always the same for same bill.
         */
        if ($eor = $fiscalizationService->getEOR()) {
            $this->fiscalizationRecord->eor = $eor;
        }

        /**
         * ZOU changes based on date of confirmation and other properties.
         */
        if ($zoi = $fiscalizationService->getZOI()) {
            $this->fiscalizationRecord->zoi = $zoi;
        }

        $this->fiscalizationRecord->save();

        return $this;
    }

    public function createFiscalizationService()
    {
        /**
         * Create business.
         */
        $business = $this->getFiscalizationBusiness();

        /**
         * Get or create FURS invoice number.
         */
        $this->fiscalizationRecord = $this->createFiscalizationRecord($business);

        /**
         * Create invoice.
         */
        $invoice = $this->createInvoice();

        return $this->order->company->createFiscalizationService($business, $invoice);
    }

    public function createFiscalizationBusiness()
    {
        $this->fiscalizationBusiness = $this->order->company->createFiscalizationBusiness();

        return $this;
    }

    public function getFiscalizationBusiness()
    {
        if (!$this->fiscalizationBusiness) {
            $this->createFiscalizationBusiness();
        }

        return $this->fiscalizationBusiness;
    }

    public function createFiscalizationRecord()
    {
        $fiscalizationRecord = (new Fiscalizations())->getOrCreateFromOrder($this->invoiceData['identifier'],
                                                                            $this->fiscalizationBusiness,
                                                                            $this->invoiceData['platform']);
        $fiscalizationRecord->invoice = $this->invoiceData['price'];
        $fiscalizationRecord->payment = $this->invoiceData['price'];

        return $fiscalizationRecord;
    }

    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function setPlatformId($platformId)
    {
        $this->platformId = $platformId;

        return $this;
    }

    public function setSaveZoi($saveZoi = true)
    {
        $this->saveZoi = $saveZoi;

        return $this;
    }

    public function getFiscalizationRecord()
    {
        return $this->fiscalizationRecord;
    }
}