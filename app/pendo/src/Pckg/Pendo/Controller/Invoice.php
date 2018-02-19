<?php namespace Pckg\Pendo\Controller;

use Exception;
use Pckg\Pendo\Record\AppKey;
use Pckg\Pendo\Record\Business;
use Pckg\Pendo\Record\Company;
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
        ];
        $invoiceData = only(post()->all(), $keys);

        /**
         * Validate posted date.
         */
        foreach ($keys as $key) {
            if (!$invoiceData[$key]) {
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
        $invoiceData['vat_number'] = $appKey->app->company->vat_number;
        $invoiceData['business'] = $appKey->app->company->business;
        $invoiceData['device'] = $appKey->app->company->device;

        /**
         * Get business or throw exception.
         */
        $business = Business::getOrFail(['company_id' => $company->id, 'business_id' => $invoiceData['business']], null,
            function() {
                throw new Exception('Business is not registered');
            });

        /**
         * Create fiscalizator and fiscalize bill.
         */
        $fiscalizator = new Fiscalizator($company->getFiscalizationConfig(),
                                         $business->createFiscalizationBusiness($invoiceData['device']),
                                         $invoiceData);
        $invoice = $fiscalizator->fiscalize();

        /**
         * Return response.
         */
        return response()->respondWithSuccess([
                                                  'invoice' => $invoice,
                                              ]);
    }

}