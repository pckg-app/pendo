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

        $softwareSupplier = '12345678';

        $url = $company->type == 'prod'
            ? 'https://blagajne.fu.gov.si:9003/v1/cash_registers'
            : 'https://blagajne-test.fu.gov.si:9002/v1/cash_registers';
        if (strtolower(substr($company->vat_number, 0, 2)) == 'hr') {
            $url = $company->type == 'prod'
                ? 'https://cis.porezna-uprava.hr:8449/FiskalizacijaService'
                : 'https://cistest.apis-it.hr:8449/FiskalizacijaServiceTest';
        }

        $key = $company->getDecodedPasswordAttribute();

        $config = new Config(
            $company->vat_number,
            $certsPath . $company->pem,
            $certsPath . $company->p12,
            $key,
            $certsPath . $company->server,
            $softwareSupplier,
            $url
        );

        if ($this->phone == 386) {
            return new Furs($config, $business, $invoice);
        } elseif ($this->phone == 385) {
            return new Purh($config, $business, $invoice);
        }
    }

}