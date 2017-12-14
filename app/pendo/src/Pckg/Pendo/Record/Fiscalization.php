<?php namespace Pckg\Pendo\Record;

use Exception;
use Pckg\Database\Record;
use Pckg\Pendo\Entity\Fiscalizations;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Pckg\Pendo\Service\Furs;

/**
 * Class Fiscalization
 *
 * @package Pckg\Furs\Record
 */
class Fiscalization extends Record
{

    /**
     * @var
     */
    protected $entity = Fiscalizations::class;

    public function queueCredit($price = null)
    {
        queue()->create('furs:credit', ['furs' => $this->id, 'price' => $price], 'manual');
    }

    public function credit($price = null)
    {
        if (is_null($price)) {
            throw new Exception('Credit price should be set');
        } else if ($price >= 0) {
            throw new Exception('Credit price should be lower than 0!');
        }

        /**
         * Create business.
         */
        $business = $this->order->company->createFiscalizationBusiness();

        /**
         * ... and also old business.
         */
        $oldBusiness = new Business(
            $this->business_id,
            $this->business_tax_number,
            null,
            $this->electronic_device_id
        );

        /**
         * We're creating new bill and referencing old one.
         * Get or create FURS invoice number.
         */
        $fursRecord = (new Fiscalizations())->createNewForOrderId($this->order_id, $business, 'credit');
        $fursRecord->invoice = $price;
        $fursRecord->payment = $price;

        /**
         * Create invoice from old data.
         */
        $invoice = new Invoice(
            $fursRecord->furs_id,
            $price,
            $price,
            date('Y-m-d') . 'T' . date('H:i:s')
        );

        /**
         * ... and old invoice.
         */
        $oldInvoice = new Invoice(
            $this->furs_id,
            $this->invoice,
            $this->payment,
            str_replace(' ', 'T', $this->requested_at)
        );

        /**
         * Create furs object.
         */
        $furs = $this->order->company->createFiscalizationService($business, $invoice);

        /**
         * Create echo message and throw exception if something is not ok.
         */
        $furs->createEchoMsg();
        $furs->postXml();

        if ($furs->getEcho() != 'vrni x') {
            throw new Exception('System is misconfigured or FURS not available!');
        }

        /**
         * Create business request.
         */
        $furs->createBusinessMsg();
        $furs->postXml();

        /**
         * Create credit request.
         */
        $furs->createCreditMsg($oldBusiness, $oldInvoice);
        $furs->postXml();

        /**
         * Set EOR code, which is always the same for same bill.
         */
        if ($eor = $furs->getEOR()) {
            $fursRecord->eor = $furs->getEOR();
        }

        /**
         * ZOU changes based on date of confirmation and other properties.
         */
        if ($zoi = $furs->getZOI()) {
            $fursRecord->zoi = $furs->getZOI();
            $fursRecord->requested_at = date('Y-m-d H:i:s');
        }

        $fursRecord->save();

        $this->type .= '_credit';
        $this->next_id = $fursRecord->id;
        $this->save();
    }

    public function queueCorrection($price = null)
    {
        queue()->create('furs:correction', ['furs' => $this->id, 'price' => $price], 'manual');
    }

    public function correction($price = null)
    {
        if (is_null($price)) {
            throw new Exception('Correction price should be set');
        }

        $defaults = config('furs');

        /**
         * ... and also old business.
         */
        $business = new Business(
            $this->business_id,
            $this->business_tax_number,
            $defaults['businessValidityDate'],
            $this->electronic_device_id
        );

        /**
         * We're creating new bill and referencing old one.
         * Get or create FURS invoice number.
         */
        $fursRecord = (new Fiscalizations())->createDuplicateForOrderId($this->order_id, $business, 'correction');
        $fursRecord->invoice = $price;
        $fursRecord->payment = $price;

        /**
         * Create invoice from data.
         */
        $invoice = new Invoice(
            $fursRecord->furs_id,
            $price,
            $price,
            str_replace(' ', 'T', $this->requested_at)
        );

        /**
         * ... and old invoice.
         */
        $oldInvoice = new Invoice(
            $this->furs_id,
            $this->invoice,
            $this->payment,
            str_replace(' ', 'T', $this->requested_at)
        );

        /**
         * Configuration
         */
        $certsPath = path('storage') . 'derive' . path('ds') . 'furs' . path('ds') . $defaults['env'] . path('ds');
        $config = new Config(
            $defaults['taxNumber'],
            $certsPath . $defaults['pemCert'],
            $certsPath . $defaults['p12Cert'],
            $defaults['password'],
            $certsPath . $defaults['serverCert'],
            $defaults['url'],
            $defaults['softwareSupplierTaxNumber']
        );

        /**
         * Create furs object.
         */
        $furs = new Furs($config, $business, $invoice);

        /**
         * Create echo message and throw exception if something is not ok.
         */
        $furs->createEchoMsg();
        $furs->postXml();

        if ($furs->getEcho() != 'vrni x') {
            throw new Exception('System is misconfigured or FURS not available!');
        }

        /**
         * Create business request.
         */
        $furs->createBusinessMsg();
        $furs->postXml();

        /**
         * Create credit request with old invoice data.
         */
        $furs->createCorrectionMsg(
            $business,
            $oldInvoice,
            (new Fiscalizations())->getAllForBusinessAndNumber(
                $business,
                $invoice->getInvoiceNumber()
            )->count()
        );
        $furs->postXml();

        /**
         * Set EOR code, which is always the same for same bill.
         */
        if ($eor = $furs->getEOR()) {
            $fursRecord->eor = $furs->getEOR();
        }

        /**
         * ZOU changes based on date of confirmation and other properties.
         */
        if ($zoi = $furs->getZOI()) {
            $fursRecord->zoi = $furs->getZOI();
            $fursRecord->requested_at = date('Y-m-d H:i:s');
        }

        $fursRecord->save();

        $this->type .= '_correction';
        $this->next_id = $fursRecord->id;
        $this->save();
    }

    public function createDuplicate($type = 'bill')
    {
        $furs = new Fiscalization(
            [
                'furs_id'              => $this->furs_id,
                'order_id'             => $this->order_id,
                'business_id'          => $this->business_id,
                'business_tax_number'  => $this->business_tax_number,
                'electronic_device_id' => $this->electronic_device_id,
                'platform_id'          => $this->platform_id,
                'type'                 => $type,
            ]
        );
        $furs->save();

        return $furs;
    }

    public function technicalStorno($price = null)
    {
        if (is_null($price)) {
            throw new Exception('Correction price should be set');
        }

        $defaults = config('furs');

        /**
         * ... and also old business.
         */
        $business = new Business(
            $this->business_id,
            $this->business_tax_number,
            $defaults['businessValidityDate'],
            $this->electronic_device_id
        );

        /**
         * We're creating new bill and referencing old one.
         * Get or create FURS invoice number.
         */
        $fursRecord = $this->createDuplicate('technical_storno');
        $fursRecord->invoice = $price;
        $fursRecord->payment = $price;

        /**
         * Create invoice from data.
         */
        $invoice = new Invoice(
            $fursRecord->furs_id,
            $price,
            $price,
            str_replace(' ', 'T', $this->requested_at)
        );

        /**
         * ... and old invoice.
         */
        $oldInvoice = new Invoice(
            $this->furs_id,
            $this->invoice,
            $this->payment,
            str_replace(' ', 'T', $this->requested_at)
        );

        /**
         * Configuration
         */
        $certsPath = path('storage') . 'derive' . path('ds') . 'furs' . path('ds') . $defaults['env'] . path('ds');
        $config = new Config(
            $defaults['taxNumber'],
            $certsPath . $defaults['pemCert'],
            $certsPath . $defaults['p12Cert'],
            $defaults['password'],
            $certsPath . $defaults['serverCert'],
            $defaults['url'],
            $defaults['softwareSupplierTaxNumber']
        );

        /**
         * Create furs object.
         */
        $furs = new Furs($config, $business, $invoice);

        /**
         * Create echo message and throw exception if something is not ok.
         */
        $furs->createEchoMsg();
        $furs->postXml();

        if ($furs->getEcho() != 'vrni x') {
            throw new Exception('System is misconfigured or FURS not available!');
        }

        /**
         * Create business request.
         */
        $furs->createBusinessMsg();
        $furs->postXml();

        /**
         * Create credit request with old invoice data.
         */
        $furs->createTechnicalStornoMsg(
            $business,
            $oldInvoice,
            (new Fiscalizations())->getAllForBusinessAndNumber(
                $business,
                $invoice->getInvoiceNumber()
            )->count()
        );
        $furs->postXml();

        /**
         * Set EOR code, which is always the same for same bill.
         */
        if ($eor = $furs->getEOR()) {
            $fursRecord->eor = $furs->getEOR();
        }

        /**
         * ZOU changes based on date of confirmation and other properties.
         */
        if ($zoi = $furs->getZOI()) {
            $fursRecord->zoi = $furs->getZOI();
            $fursRecord->requested_at = date('Y-m-d H:i:s');
        }

        $fursRecord->save();

        $this->type .= '_technical_storno';
        $this->next_id = $fursRecord->id;
        $this->save();
    }

    public function queueStorno()
    {
        queue()->create('furs:storno', ['furs' => $this->id], 'manual');
    }

    public function storno()
    {
    }

    public function getNumAttribute()
    {
        return $this->business_id . '-' . $this->electronic_device_id . '-' . $this->furs_id;
    }

}