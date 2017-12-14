<?php namespace Pckg\Pendo\Controller;

use Exception;
use Pckg\Pendo\Record\Business as BusinessRecord;
use Pckg\Pendo\Record\Company as CompanyRecord;

/**
 * Class Business
 *
 * @package Pckg\Pendo\Controller
 */
class Business
{

    /**
     * @return \Pckg\Framework\Response
     * @throws Exception
     */
    public function postRegisterAction()
    {
        /**
         * Get posted data.
         */
        $businessData = only(post()->all(), ['company_id', 'business_id']);

        /**
         * Check if business is already registered.
         */
        if (BusinessRecord::gets($businessData)) {
            throw new Exception('Business is already registered');
        }

        /**
         * Create new business.
         */
        $business = CompanyRecord::create($businessData);

        /**
         * Return company.
         */
        return response()->respondWithSuccess(['business' => $business]);
    }

}