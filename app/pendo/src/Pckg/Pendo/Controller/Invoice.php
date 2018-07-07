<?php namespace Pckg\Pendo\Controller;

use Exception;
use Pckg\Pendo\Record\AppKey;
use Pckg\Pendo\Service\Fiscalizator;

/**
 * Class Invoice
 *
 * @package Pckg\Pendo\Controller
 */
class Invoice
{

    /**
     * @throws Exception
     */
    public function postConfirmAction(AppKey $appKey)
    {
        /**
         * Get posted data.
         */
        $keys = [
            'datetime',
            'platform',
            'identifier',
            'total',
            'payment',
            'taxes',
            'person',
            'business',
            'device',
        ];
        $invoiceData = only(post()->all(), $keys);
        dd($invoiceData);

        /**
         * Validate posted date.
         */
        foreach ($keys as $key) {
            if (!array_key_exists($key, $invoiceData)) {
                throw new Exception($key . ' is required');
            }
        }

        /**
         * Get company or throw exception.
         */
        $company = $appKey->app->company;
        if (!$company) {
            throw new Exception("First register company");
        }

        /**
         * Get some data from apiKey.
         */
        $invoiceData['vat_number'] = substr($company->vat_number, 2);

        /**
         * Create fiscalizator and fiscalize bill.
         */
        $fiscalizator = new Fiscalizator($company, $invoiceData);
        $fiscalizator->fiscalize();

        /**
         * Return response.
         */
        return [
            'invoice' => $fiscalizator->getFiscalizationRecord(),
        ];
    }

}