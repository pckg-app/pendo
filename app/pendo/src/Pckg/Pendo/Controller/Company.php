<?php namespace Pckg\Pendo\Controller;

use Exception;
use Pckg\Pendo\Record\Company as CompanyRecord;

/**
 * Class Company
 *
 * @package Pckg\Pendo\Controller
 */
class Company
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
        $companyData = only(post()->all(), ['vat_number', 'validity_date']);

        /**
         * Check if company is already registered.
         */
        if (Company::gets(['vat_number' => $companyData['vat_number']])) {
            throw new Exception('Company is already registered');
        }

        /**
         * Create new company.
         */
        $company = CompanyRecord::create($companyData);

        /**
         * Return company.
         */
        return response()->respondWithSuccess(['company' => $company]);
    }

}