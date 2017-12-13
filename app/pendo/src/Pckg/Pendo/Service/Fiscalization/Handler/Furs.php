<?php namespace Pckg\Pendo\Service\Fiscalization\Handler;

use Pckg\Pendo\Service\Fiscalization\AbstractHandler;

class Furs extends AbstractHandler
{

    public function fiscalize()
    {
        $furs = $this->createFiscalizationService();
        /**
         * Create invoice request.
         */
        $furs->createInvoiceMsg();
        $furs->postXml();

        /**
         * Generate QR code.
         */
        if ($qrFile = $furs->generateQR()) {
            $this->order->fiscalization_qr = $qrFile;
        }

        /**
         * Set EOR code, which is always the same for same bill.
         */
        if ($eor = $furs->getEOR()) {
            $this->order->furs_eor = $furs->getEOR();
            $this->fiscalizationRecord->eor = $this->order->furs_eor;
        }

        /**
         * ZOU changes based on date of confirmation and other properties.
         */
        if ($zoi = $furs->getZOI()) {
            if ($this->saveZoi) {
                $this->order->furs_zoi = $furs->getZOI();
                $this->order->furs_confirmed_at = date('Y-m-d H:i:s');
                $this->order->furs_num = $this->fiscalizationRecord->business_id . '-' .
                                         $this->fiscalizationRecord->electronic_device_id . '-' .
                                         $this->fiscalizationRecord->furs_id;
                $this->order->furs_confirmed_for = $this->price;
            }
            $this->fiscalizationRecord->zoi = $this->order->furs_zoi;
        }

        $this->order->save();
        $this->fiscalizationRecord->save();

        return $this;
    }

}