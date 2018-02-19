<?php namespace Pckg\Pendo\Record;

use Pckg\Database\Record;
use Pckg\Pendo\Entity\Countries;
use Pckg\Pendo\Service\Fiscalization\Business;
use Pckg\Pendo\Service\Fiscalization\Config;
use Pckg\Pendo\Service\Fiscalization\Invoice;
use Pckg\Pendo\Service\Fiscalization\Service\Furs;
use Pckg\Pendo\Service\Fiscalization\Service\Purh;

class Country extends Record
{

    protected $entity = Countries::class;

    public function createFiscalizationService(Business $business, Invoice $invoice = null, Company $company)
    {
        /**
         * Configuration
         */
        $certsPath = path('app_private') . 'company/certificate' . path('ds');

        $code = 'sl';
        $softwareSupplier = '1234567(';
        if ($this->phone == 385) {
            $code = 'hr';
        }

        $key = $company->getDecodedPasswordAttribute();

        $config = new Config(
            $company->vat_number,
            $certsPath . $company->pem,
            $certsPath . $company->p12,
            $key,
            $certsPath . $company->server,
            $softwareSupplier
        );

        if ($this->phone == 386) {
            return new Furs($config, $business, $invoice);
        } elseif ($this->phone == 385) {
            return new Purh($config, $business, $invoice);
        }
    }

}