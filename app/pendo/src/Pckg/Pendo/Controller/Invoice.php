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
            'operator',
            'business',
            'device',
        ];
        $invoiceData = only(post()->all(), $keys);

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
         * Set handler.
         */
        $invoiceData['handler'] = $company->country->code === 'SI' ? 'furs' : 'purh';

        /**
         * And mode.
         */
        $invoiceData['mode'] = $company->type;

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