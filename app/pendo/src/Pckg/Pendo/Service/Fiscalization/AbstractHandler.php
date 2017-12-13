<?php namespace Pckg\Pendo\Service\Fiscalization;

use Derive\Orders\Record\Order;
use Pckg\Fiscalization\Entity\Fiscalizations;

abstract class AbstractHandler
{

    /**
     * @var Order
     */
    protected $order;

    protected $price;

    protected $platformId;

    protected $saveZoi;

    /**
     * @var \Pckg\Fiscalization\Record\Fiscalization
     */
    protected $fiscalizationRecord;

    protected $fiscalizationBusiness;

    public abstract function fiscalize();

    public function createInvoice()
    {
        return new Invoice(
            $this->fiscalizationRecord->furs_id,
            number_format($this->price, 2),
            number_format($this->price, 2),
            date('Y-m-d') . 'T' . date('H:i:s')
        );
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

    public function createFiscalizationRecord(Business $business)
    {
        $fursRecord = (new Fiscalizations())->getOrCreateFromOrder($this->order, $business, $this->platformId);
        $fursRecord->invoice = $this->price;
        $fursRecord->payment = $this->price;

        return $fursRecord;
    }

    public function setOrder(Order $order)
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