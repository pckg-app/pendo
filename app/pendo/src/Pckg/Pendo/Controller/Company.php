<?php namespace Pckg\Pendo\Controller;

use Exception;
use Pckg\Pendo\Form\RegisterCompany;
use Pckg\Pendo\Record\AppKey;
use Pckg\Pendo\Record\Company as CompanyRecord;

/**
 * Class Company
 *
 * @package Pckg\Pendo\Controller
 */
class Company
{

    /**
     * @throws Exception
     */
    public function postRegisterAction(RegisterCompany $registerCompany, AppKey $appKey)
    {
        /**
         * Get posted data.
         */
        $companyData = $registerCompany->getData();

        /**
         * Create new company.
         */
        $company = CompanyRecord::getOrCreate(only($companyData, ['vat_number']), null, array_merge($companyData, ['country_id' => 1]));

        /**
         * Return company.
         */
        return [
            'success' => true,
            'company' => only($company, ['id', 'vat_number']),
        ];
    }

}