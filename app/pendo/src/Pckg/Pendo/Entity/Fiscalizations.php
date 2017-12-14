<?php namespace Pckg\Pendo\Entity;

use Pckg\Database\Collection;
use Pckg\Database\Entity;
use Pckg\Pendo\Record\Fiscalization;
use Pckg\Pendo\Service\Fiscalization\Business;

class Fiscalizations extends Entity
{

    protected $record = Fiscalization::class;

    public function next()
    {
        return $this->belongsTo(Fiscalizations::class)
                    ->foreignKey('next_id');
    }

    public function getAllForBusiness(Business $business)
    {
        return (new static())
            ->where('business_id', $business->getId())
            ->where('business_tax_number', $business->getTaxNumber())
            ->where('electronic_device_id', $business->getElectronicDeviceId())
            ->orderBy('id ASC')
            ->all();
    }

    /**
     * @param Business $business
     * @param          $number
     *
     * @return Collection
     */
    public function getAllForBusinessAndNumber(Business $business, $number)
    {
        return (new static())
            ->where('business_id', $business->getId())
            ->where('business_tax_number', $business->getTaxNumber())
            ->where('electronic_device_id', $business->getElectronicDeviceId())
            ->where('furs_id', $number)
            ->orderBy('id ASC')
            ->all();
    }

    public function getLastForBusiness(Business $business)
    {
        return (new static())
            ->where('business_id', $business->getId())
            ->where('business_tax_number', $business->getTaxNumber())
            ->where('electronic_device_id', $business->getElectronicDeviceId())
            ->where('requested_at', date('Y') . '-01-01 00:00:00', '>=')
            ->orderBy('furs_id DESC')
            ->one();
    }

    public function getFirstForBusinessOrder(Business $business, $orderId, $platformId)
    {
        return (new static())
            ->where('business_id', $business->getId())
            ->where('business_tax_number', $business->getTaxNumber())
            ->where('electronic_device_id', $business->getElectronicDeviceId())
            ->where('order_id', $orderId)
            ->where('platform_id', $platformId)
            ->orderBy('furs_id ASC')
            ->one();
    }

    public function createNewForOrder($orderId, Business $business, $platformId = null)
    {
        if (!$platformId) {
            $platformId = config('app');
        }

        $last = $this->getLastForBusiness($business);

        return new Fiscalization(
            [
                'furs_id'              => $last ? $last->furs_id + 1 : 1,
                'order_id'             => $orderId,
                'business_id'          => $business->getId(),
                'business_tax_number'  => $business->getTaxNumber(),
                'electronic_device_id' => $business->getElectronicDeviceId(),
                'platform_id'          => $platformId,
            ]
        );
    }

    /**
     * @param          $orderId
     * @param Business $business
     * @param string   $type
     *
     * @return Fiscalization
     */
    public function createNewForOrderId($orderId, Business $business, $type = 'bill')
    {
        $last = $this->getLastForBusiness($business);

        return Fiscalization::create(
            [
                'furs_id'              => $last ? $last->furs_id + 1 : 1,
                'order_id'             => $orderId,
                'business_id'          => $business->getId(),
                'business_tax_number'  => $business->getTaxNumber(),
                'electronic_device_id' => $business->getElectronicDeviceId(),
                'platform_id'          => config('app'),
                'type'                 => $type,
            ]
        );
    }

    public function createDuplicateForOrderId($orderId, Business $business, $type = 'bill', $platformId = null)
    {
        if (!$platformId) {
            $platformId = config('app');
        }

        $last = $this->getFirstForBusinessOrder($business, $orderId, $platformId);

        return new Fiscalization(
            [
                'furs_id'              => $last->furs_id,
                'order_id'             => $orderId,
                'business_id'          => $business->getId(),
                'business_tax_number'  => $business->getTaxNumber(),
                'electronic_device_id' => $business->getElectronicDeviceId(),
                'platform_id'          => $platformId,
                'type'                 => $type,
            ]
        );
    }

    public function getOrCreateFromOrder($orderId, Business $business, $platformId = null)
    {
        if (!$platformId) {
            $platformId = config('app');
        }
        /**
         * Get last furs record
         */
        $furs = (new static())
            ->where('order_id', $orderId)
            ->where('business_id', $business->getId())
            ->where('business_tax_number', $business->getTaxNumber())
            ->where('electronic_device_id', $business->getElectronicDeviceId())
            ->where('platform_id', $platformId)
            ->orderBy('id DESC')
            ->one();

        if (!$furs) {
            $furs = $this->createNewForOrder($orderId, $business, $platformId);
        }

        $furs->requested_at = date('Y-m-d H:i:s');
        $furs->type = 'bill';
        $furs->save();

        return $furs;
    }

}