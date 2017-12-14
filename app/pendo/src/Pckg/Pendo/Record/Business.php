<?php namespace Pckg\Pendo\Record;

use Pckg\Database\Record;
use Pckg\Pendo\Entity\Businesses;
use Pckg\Pendo\Service\Fiscalization\Business as FiscalizationBusiness;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;

class Business extends Record
{

    protected $entity = Businesses::class;

    public function createFiscalizationBusiness($device)
    {
        return new FiscalizationBusiness(
            $this->business_id,
            substr($this->company->vat_number, 2), // remove SI, HR from starting of vat number
            date('Y-m-d', strtotime($this->company->incorporated_at)),
            $device
        );
    }

    /**
     * @param Company               $company
     * @param FiscalizationBusiness $business
     * @param Invoice               $invoice
     *
     * @return Furs|Purh
     */
    public function createFiscalizationService(Company $company)
    {
        /**
         * Configuration
         */
        $certsPath = path('app_private') . 'certs' . path('ds');

        $code = $company->getCountryCode();

        $key = $company->getCertificatePassword();

        $config = new Config(
            $company->vat_number,
            $certsPath . $company->pem_cert,
            $certsPath . $company->p12_cert,
            $key,
            $certsPath . $company->server_cert,
            config('derive.fiscalization.settings.' . $code . '.url'),
            config('derive.fiscalization.settings.' . $code . '.softwareSupplierTaxNumber')
        );

        return $company->getFiscalizationHandler($config, $business);
    }

}